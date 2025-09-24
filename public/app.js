// =======================
// Sidebar toggle (submenu)
// =======================
document.addEventListener("DOMContentLoaded", function () {
  const toggles = document.querySelectorAll(".sidebar-toggle");
  toggles.forEach(toggle => {
    toggle.addEventListener("click", () => {
      toggle.classList.toggle("active");
      const submenu = toggle.nextElementSibling;
      if (submenu) submenu.classList.toggle("open");
    });
  });

  // Ejecutar hooks al cargar la página por primera vez
  afterContentLoad();
  
  // =======================
  // 🔎 Buscador Global
  // =======================
  const input = document.getElementById("globalSearchInput");
  const resultsBox = document.getElementById("globalSearchResults");

  if (input && resultsBox) {
    input.addEventListener("keyup", function() {
      let q = this.value.trim();

      if (q.length < 2) {
        resultsBox.style.display = "none";
        resultsBox.innerHTML = "";
        return;
      }

      fetch("sections/ajax_search.php?q=" + encodeURIComponent(q))
        .then(res => res.json())
        .then(data => {
          console.log("📌 Respuesta del servidor:", data);

          // Inicializar HTML
          let html = "";

          // 👉 Clientes
          if (data.clientes && data.clientes.length > 0) {
            html += `<div class="list-group-item active">👤 Clientes</div>`;
            data.clientes.forEach(c => {
              html += `<a href="${c.link}" class="list-group-item">
                          <strong>${c.nombre}</strong><br>
                          ${c.correo} | ${c.telefono}
                       </a>`;
            });
          }

          // 👉 Productos
          if (data.productos && data.productos.length > 0) {
            html += `<div class="list-group-item active">📦 Productos</div>`;
            data.productos.forEach(p => {
              html += `<a href="${p.link}" class="list-group-item">
                          <strong>${p.nombre}</strong><br>
                          ${p.descripcion || ""}<br>
                          Precio: $${p.precio} | Stock: ${p.stock}
                       </a>`;
            });
          }

          // 👉 Ventas
          if (data.ventas && data.ventas.length > 0) {
            html += `<div class="list-group-item active">🧾 Ventas</div>`;
            data.ventas.forEach(v => {
              html += `<a href="${v.link}" class="list-group-item">
                          Venta #${v.id} - Cliente: ${v.cliente || "N/A"}<br>
                          Total: $${v.total} | Fecha: ${v.fecha}
                       </a>`;
            });
          }

          if (html === "") {
            html = `<div class="list-group-item text-muted">Sin resultados</div>`;
          }

          resultsBox.innerHTML = html;
          resultsBox.style.display = "block";
        })
        .catch(err => {
          console.error("Error en buscador:", err);
        });
    });
  }

// =======================
  // Validación Reportes Ventas
  // =======================
  const formReportes = document.querySelector("form.filtros");
  if (formReportes) {
    formReportes.addEventListener("submit", function (e) {
      const fechaInicio = formReportes.querySelector('input[name="fecha_inicio"]').value;
      const fechaFin = formReportes.querySelector('input[name="fecha_fin"]').value;

      if (!fechaInicio || !fechaFin) {
        e.preventDefault(); // evita que se envíe el form
        alert("⚠️ Debes seleccionar una fecha de inicio y una fecha de fin antes de generar el reporte.");
      }
    });
  }
});

// =======================
// After load hook
// Detecta qué sección está abierta y activa el CRUD correcto
// =======================
function afterContentLoad() {
  // Productos
  if (document.getElementById("products-table-body")) {
    setupProductosCRUD();
  }

  // Clientes
  if (document.getElementById("clients-table-body")) {
    setupClientesCRUD();
  }

  // Inventario
if (document.getElementById("form-movimiento")) {
  setupInventarioCRUD();
}

  // Ventas
  if (document.getElementById("form-venta")) {
    setupVentasCRUD();
  }

  // Reportes
if (document.getElementById("reportes-ventas-body")) {
  setupReportes();
}
}

// =======================
// CRUD Products + Fetch
// =======================
let editingProductId = null;

function fetchProductos(page = 1, search = "") {
  const tableBody = document.getElementById("products-table-body");
  const pagination = document.getElementById("pagination-products");
  if (!tableBody) return;

  fetch("sections/ajax_products.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "action=fetch&page=" + page + "&search=" + encodeURIComponent(search),
  })
    .then((r) => r.json())
    .then((data) => {
      tableBody.innerHTML = "";
      if (data.productos) {
        data.productos.forEach((p) => {
          const tr = document.createElement("tr");
          tr.dataset.id = p.id;
          tr.innerHTML = `
            <td>${p.id}</td>
            <td class="p-nombre">
              <div style="display:flex;align-items:center;gap:8px;">
                ${
                  p.imagen
                    ? `<img src="uploads/${p.imagen}" alt="producto"
                         style="width:40px;height:40px;border-radius:6px;object-fit:cover;">`
                    : ""
                }
                <span>${escapeHtml(p.nombre)}</span>
              </div>
            </td>
            <td>${p.marca}</td> <!-- 👈 Aquí agregamos la marca -->
            <td class="p-categoria">${escapeHtml(p.categoria || "")}</td>
            <td class="p-precio">$${parseFloat(p.precio).toFixed(2)}</td>
            <td class="p-stock">${p.stock}</td>
            <td class="p-fecha-ingreso">${p.fecha_ingreso || "—"}</td>
            <td>
              <button class="btn-edit">Editar</button>
              <button class="btn-delete">Eliminar</button>
            </td>
          `;
          tableBody.appendChild(tr);
        });
      }

      // Paginación
      if (pagination) {
        pagination.innerHTML = "";
        for (let i = 1; i <= data.pages; i++) {
          const btn = document.createElement("button");
          btn.textContent = i;
          if (i === page) btn.classList.add("active");
          btn.addEventListener("click", () => fetchProductos(i, search));
          pagination.appendChild(btn);
        }
      }
    });
}

function setupProductosCRUD() {
  const form = document.getElementById("form-producto");
  const tableBody = document.getElementById("products-table-body");
  const searchBox = document.getElementById("search-product");

  if (!form || !tableBody) return;

  // === Autocompletado de Marca producto ===
  const marcaInput = document.getElementById("prod-marca");
  const suggestionsBox = document.getElementById("marca-suggestions");

  if (marcaInput && suggestionsBox) {
    marcaInput.addEventListener("input", () => {
      const q = marcaInput.value.trim();
      if (q.length < 2) {
        suggestionsBox.innerHTML = "";
        suggestionsBox.style.display = "none";
        return;
      }

      fetch("sections/ajax_marcas.php?q=" + encodeURIComponent(q))
        .then((r) => r.json())
        .then((data) => {
          suggestionsBox.innerHTML = "";
          if (data.length > 0) {
            data.forEach((m) => {
              const div = document.createElement("div");
              div.textContent = m.nombre || m; // soporta string o {nombre}
              div.classList.add("suggestion-item");
              div.addEventListener("click", () => {
                marcaInput.value = m.nombre || m;
                suggestionsBox.innerHTML = "";
                suggestionsBox.style.display = "none";
              });
              suggestionsBox.appendChild(div);
            });
            suggestionsBox.style.display = "block";
          } else {
            suggestionsBox.style.display = "none";
          }
        })
        .catch((err) => console.error("Error autocompletado marcas:", err));
    });
  }

  // === Búsqueda de productos ===
  if (searchBox) {
    searchBox.addEventListener("input", () => {
      fetchProductos(1, searchBox.value);
    });
  }

  // === Cargar inicial ===
  fetchProductos();

  // === Guardar producto ===
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    formData.append("action", editingProductId ? "update" : "create");
    if (editingProductId) formData.append("id", editingProductId);

    fetch("sections/ajax_products.php", {
      method: "POST",
      body: formData,
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.error) {
          alert(data.error);
          return;
        }
        form.reset();
        editingProductId = null;
        form.querySelector("#btn-create-product").textContent = "Agregar producto";
        fetchProductos();
      })
      .catch(() => alert("Error al guardar producto"));
  });

  // === Editar / Eliminar producto ===
  tableBody.addEventListener("click", (e) => {
    const row = e.target.closest("tr");
    if (!row) return;

    if (e.target.closest(".btn-edit")) {
      editingProductId = row.dataset.id;
      form.querySelector("#prod-nombre").value =
        row.querySelector(".p-nombre span").innerText.trim();
      form.querySelector("#prod-marca").value = row.querySelector(".p-marca")
        ? row.querySelector(".p-marca").innerText.trim()
        : ""; // 👈 ahora también carga marca
      form.querySelector("#prod-categoria").value =
        row.querySelector(".p-categoria").innerText.trim();
      form.querySelector("#prod-precio").value =
        row.querySelector(".p-precio").innerText.replace("$", "");
      form.querySelector("#prod-stock").value =
        row.querySelector(".p-stock").innerText;
      form.querySelector("#prod-fecha-ingreso").value =
        row.querySelector(".p-fecha-ingreso").innerText !== "—"
          ? row.querySelector(".p-fecha-ingreso").innerText
          : "";
      form.querySelector("#prod-descripcion").value = "";
      form.querySelector("#btn-create-product").textContent = "Actualizar producto";
    }

    if (e.target.closest(".btn-delete")) {
      if (!confirm("¿Seguro que deseas eliminar este producto?")) return;
      fetch("sections/ajax_products.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "action=delete&id=" + encodeURIComponent(row.dataset.id),
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) fetchProductos();
          else alert(data.error || "Error al eliminar producto");
        });
    }
  });
}


// =======================
// CRUD Clientes + Fetch
// =======================
function fetchClientes(page = 1, search = "") {
  const tableBody = document.getElementById("clients-table-body");
  const pagination = document.getElementById("pagination-clients");
  if (!tableBody) return;

  fetch("sections/ajax_clients.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "action=fetch&page=" + page + "&search=" + encodeURIComponent(search),
  })
    .then((r) => r.json())
    .then((data) => {
      tableBody.innerHTML = "";
      if (data.clientes) {
        data.clientes.forEach((c) => {
          const tr = document.createElement("tr");
          tr.dataset.id = c.id;
          tr.innerHTML = `
            <td>${c.id}</td>
            <td class="c-nombre">${c.nombre}</td>
            <td class="c-correo">${c.correo}</td>
            <td class="c-telefono">${c.telefono}</td>
            <td class="c-direccion">${c.direccion}</td>
            <td class="c-fecha">${c.fecha_registro}</td>
            <td>
              <button class="btn-edit">Editar</button>
              <button class="btn-delete">Eliminar</button>
            </td>
          `;
          tableBody.appendChild(tr);
        });
      }

      // Paginación
      if (pagination) {
        pagination.innerHTML = "";
        for (let i = 1; i <= data.pages; i++) {
          const btn = document.createElement("button");
          btn.textContent = i;
          if (i === page) btn.classList.add("active");
          btn.addEventListener("click", () => fetchClientes(i, search));
          pagination.appendChild(btn);
        }
      }
    });
}

function setupClientesCRUD() {
  const form = document.getElementById("form-cliente");
  const tableBody = document.getElementById("clients-table-body");
  const searchBox = document.getElementById("search-client");
  let editingClientId = null;

  if (!form || !tableBody) return;

  // Búsqueda
  if (searchBox) {
    searchBox.addEventListener("input", () => {
      fetchClientes(1, searchBox.value);
    });
  }

  // Cargar inicial
  fetchClientes();

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    formData.append("action", editingClientId ? "update" : "create");
    if (editingClientId) formData.append("id", editingClientId);

    fetch("sections/ajax_clients.php", { method: "POST", body: formData })
      .then((r) => r.json())
      .then((data) => {
        if (data.error) return alert(data.error);
        form.reset();
        editingClientId = null;
        form.querySelector("#btn-create-cliente").textContent = "Agregar cliente";
        fetchClientes();
      });
  });

  tableBody.addEventListener("click", (e) => {
    const row = e.target.closest("tr");
    if (!row) return;

    if (e.target.classList.contains("btn-edit")) {
      editingClientId = row.dataset.id;
      form.querySelector("#cli-nombre").value = row.querySelector(".c-nombre").innerText;
      form.querySelector("#cli-correo").value = row.querySelector(".c-correo").innerText;
      form.querySelector("#cli-telefono").value = row.querySelector(".c-telefono").innerText;
      form.querySelector("#cli-direccion").value = row.querySelector(".c-direccion").innerText;
      form.querySelector("#btn-create-cliente").textContent = "Actualizar cliente";
    }

    if (e.target.classList.contains("btn-delete")) {
      if (!confirm("¿Eliminar este cliente?")) return;
      fetch("sections/ajax_clients.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "action=delete&id=" + encodeURIComponent(row.dataset.id),
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) fetchClientes();
          else alert(data.error || "Error al eliminar cliente");
        });
    }
  });
}

// =======================
// CRUD Ventas
// =======================
function setupVentasCRUD() {
  const form = document.getElementById("form-venta");
  const selectCliente = document.getElementById("venta-cliente");
  const selectProducto = document.getElementById("venta-producto");
  const inputCantidad = document.getElementById("venta-cantidad");
  const btnAdd = document.getElementById("btn-add-producto");
  const carritoBody = document.getElementById("venta-items");
  const totalSpan = document.getElementById("venta-total");
  const ventasBody = document.getElementById("ventas-table-body");
  const pagination = document.getElementById("pagination-ventas");

  // 👇 nuevos elementos
  const selectModalidad = document.getElementById("venta-modalidad");
  const vencimientoGroup = document.getElementById("credito-extra");
  const inputVencimiento = document.getElementById("venta-fecha-vencimiento");
  const inputAbono = document.getElementById("venta-abono"); // 👈 opcional

  if (!form || !selectCliente || !selectProducto) return;

  let carrito = [];
  let productos = [];

  // === Mostrar/ocultar vencimiento ===
  if (selectModalidad && vencimientoGroup) {
    selectModalidad.addEventListener("change", () => {
      if (selectModalidad.value === "credito") {
        vencimientoGroup.style.display = "block";
      } else {
        vencimientoGroup.style.display = "none";
        if (inputVencimiento) inputVencimiento.value = "";
      }
    });
  }

  // === Cargar combos de clientes y productos ===
  fetch("sections/ajax_ventas.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "action=loadCombos",
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.clientes) {
        data.clientes.forEach((c) => {
          const opt = document.createElement("option");
          opt.value = c.id;
          opt.textContent = c.nombre;
          selectCliente.appendChild(opt);
        });
      }
      if (data.productos) {
        productos = data.productos;
        data.productos.forEach((p) => {
          const opt = document.createElement("option");
          opt.value = p.id;
          opt.textContent = p.nombre + " ($" + parseFloat(p.precio).toFixed(2) + ")";
          selectProducto.appendChild(opt);
        });
      }
    });

  // === Agregar al carrito ===
  btnAdd.addEventListener("click", () => {
    const prodId = selectProducto.value;
    const cantidad = parseInt(inputCantidad.value, 10);

    if (!prodId || isNaN(cantidad) || cantidad <= 0) {
      alert("Selecciona un producto y cantidad válida");
      return;
    }

    // 👇 Validar fecha de vencimiento si es crédito
    const modalidadPago = selectModalidad.value;
    if (modalidadPago === "credito") {
      const fechaVencimiento = inputVencimiento.value;
      if (!fechaVencimiento) {
        alert("Debes seleccionar una fecha de vencimiento para crédito.");
        return;
      }
    }

    const prod = productos.find((p) => p.id == prodId);
    if (!prod) return;

    const existente = carrito.find((item) => item.id == prodId);
    if (existente) {
      existente.cantidad += cantidad;
    } else {
      carrito.push({
        id: prod.id,
        nombre: prod.nombre,
        marca: prod.marca,
        precio: parseFloat(prod.precio),
        cantidad,
      });
    }

    inputCantidad.value = "";
    renderCarrito();
  });

  function renderCarrito() {
    carritoBody.innerHTML = "";
    let total = 0;
    carrito.forEach((item, idx) => {
      const subtotal = item.precio * item.cantidad;
      total += subtotal;
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${item.nombre}</td>
        <td>${item.marca || "—"}</td>
        <td>${item.cantidad}</td>
        <td>$${item.precio.toFixed(2)}</td>
        <td>$${subtotal.toFixed(2)}</td>
        <td><button class="btn-delete" data-idx="${idx}">❌</button></td>
      `;
      carritoBody.appendChild(tr);
    });
    totalSpan.textContent = total.toFixed(2);

    carritoBody.querySelectorAll(".btn-delete").forEach((btn) => {
      btn.addEventListener("click", () => {
        const idx = btn.dataset.idx;
        carrito.splice(idx, 1);
        renderCarrito();
      });
    });
  }

  // === Confirmar venta ===
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    if (carrito.length === 0) {
      alert("Agrega productos al carrito primero.");
      return;
    }

    const clienteId = selectCliente.value;
    const modalidadPago = selectModalidad.value;
    const fechaVencimiento =
      modalidadPago === "credito" ? inputVencimiento.value : null;
    const abono =
      modalidadPago === "credito" && inputAbono
        ? parseFloat(inputAbono.value) || 0
        : 0;

    if (modalidadPago === "credito" && !fechaVencimiento) {
      alert("Debes seleccionar una fecha de vencimiento para crédito.");
      return;
    }

    const payload = {
      action: "create",
      cliente_id: clienteId,
      items: carrito,
      modalidad_pago: modalidadPago,
      fecha_vencimiento: fechaVencimiento,
      abono: abono,
    };

    fetch("sections/ajax_ventas.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          alert("Venta registrada correctamente ✅");
          carrito = [];
          renderCarrito();
          fetchVentas();
        } else {
          alert(data.error || "Error al registrar la venta");
        }
      })
      .catch((err) => console.error("Error Confirmar Venta:", err));
  });

  // === Historial de ventas ===
  function fetchVentas(page = 1) {
    fetch("sections/ajax_ventas.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=fetch&page=" + page,
    })
      .then((r) => r.json())
      .then((data) => {
        ventasBody.innerHTML = "";
        if (data.ventas) {
          data.ventas.forEach((v) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${v.id}</td>
              <td>${v.cliente}</td>
              <td>${v.marcas || "—"}</td> <!-- 👈 nueva columna -->
              <td>${v.fecha}</td>
              <td>$${parseFloat(v.total).toFixed(2)}</td>
              <td>${v.modalidad_pago}</td>
              <td>${v.fecha_vencimiento || "—"}</td>
              <td>$${parseFloat(v.pagado).toFixed(2)}</td>
              <td>$${parseFloat(v.saldo).toFixed(2)}</td>
            `;
            ventasBody.appendChild(tr);
          });
        }

        // Paginación
        if (pagination) {
          pagination.innerHTML = "";
          for (let i = 1; i <= data.pages; i++) {
            const btn = document.createElement("button");
            btn.textContent = i;
            if (i === page) btn.classList.add("active");
            btn.addEventListener("click", () => fetchVentas(i));
            pagination.appendChild(btn);
          }
        }
      });
  }

  // Carga inicial
  fetchVentas();
}


// =======================
// Mostrar/Ocultar columnas en Reportes Ventas
// =======================
document.addEventListener("DOMContentLoaded", function () {
  const columnas = document.querySelectorAll(".columnas input[type='checkbox']");
  const tabla = document.querySelector("#reportes-container table");

  if (columnas.length && tabla) {
    columnas.forEach((chk, index) => {
      chk.addEventListener("change", () => {
        const colIndex = index; // misma posición que en la tabla
        const filas = tabla.querySelectorAll("tr");

        filas.forEach(fila => {
          const celdas = fila.querySelectorAll("th, td");
          if (celdas[colIndex]) {
            celdas[colIndex].style.display = chk.checked ? "" : "none";
          }
        });
      });
    });
  }
});

// =======================
// Reportes - Botón limpiar
// =======================
function setupReportes() {
  const btnLimpiar = document.getElementById("btn-limpiar-reporte");
  const reportesBody = document.getElementById("reportes-ventas-body");

  if (btnLimpiar && reportesBody) {
    btnLimpiar.addEventListener("click", () => {
      reportesBody.innerHTML = ""; 
      alert("Reporte limpiado ✅");
    });
  }
}

// =======================
// CRUD Inventario (Entradas/Salidas)
// =======================
function setupInventarioCRUD() {
  const form = document.getElementById("form-movimiento");
  const tableBody = document.getElementById("inventory-table-body");
  const uploadForm = document.getElementById("form-upload-excel"); // 👈 nuevo

  if (!form || !tableBody) return;

  // === Autocompletado de Marca en Inventario ===
  const marcaInput = document.getElementById("inv-marca");
  const suggestionsBox = document.getElementById("inv-marca-suggestions");

  if (marcaInput && suggestionsBox) {
    marcaInput.addEventListener("input", () => {
      const q = marcaInput.value.trim();
      if (q.length < 2) {
        suggestionsBox.innerHTML = "";
        suggestionsBox.style.display = "none";
        return;
      }

      fetch("sections/ajax_marcas.php?q=" + encodeURIComponent(q))
        .then((r) => r.json())
        .then((data) => {
          suggestionsBox.innerHTML = "";
          if (data.length > 0) {
            data.forEach((m) => {
              const div = document.createElement("div");
              div.textContent = m;
          div.addEventListener("click", () => {
          marcaInput.value = m;
          suggestionsBox.innerHTML = "";
          suggestionsBox.style.display = "none";
});
              suggestionsBox.appendChild(div);
            });
            suggestionsBox.style.display = "block";
          } else {
            suggestionsBox.style.display = "none";
          }
        })
        .catch((err) => console.error("Error autocompletado marcas:", err));
    });
  }

  // === Registrar movimiento ===
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    formData.append("action", "addMovement"); // 👈 usar el mismo nombre que ajax_inventory.php

    fetch("sections/ajax_inventory.php", {
      method: "POST",
      body: formData,
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          alert("Movimiento registrado ✅");
          form.reset();
          fetchMovimientos(); // 👈 mejor que recargar toda la página
        } else {
          alert(data.error || "Error al registrar movimiento");
        }
      })
      .catch((err) => {
        console.error("Error en Inventario:", err);
      });
  });

  // === Listar movimientos ===
  function fetchMovimientos() {
    fetch("sections/ajax_inventory.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "action=fetchMovements",
    })
      .then((r) => r.json())
      .then((data) => {
        tableBody.innerHTML = "";
        if (data.movimientos) {
          data.movimientos.forEach((m) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${m.id}</td>
              <td>${m.producto}</td>
              <td>${m.tipo === "entrada" ? "📥 Entrada" : "📤 Salida"}</td>
              <td>${m.cantidad}</td>
              <td>${m.fecha}</td>
              <td>${m.marca ?? ""}</td>
              <td>${m.usuario}</td>
            `;
            tableBody.appendChild(tr);
          });
        }
      });
  }


  // === Carga masiva desde Excel ===
  if (uploadForm) {
    uploadForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const formData = new FormData(uploadForm);

      fetch("sections/ajax_inventory_upload.php", {
        method: "POST",
        body: formData,
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            alert(`✅ ${data.insertados} movimientos cargados desde Excel`);
            fetchMovimientos(); // refrescar lista sin recargar la página
            uploadForm.reset();
          } else {
            alert("⚠️ " + (data.error || "Error al procesar archivo"));
          }
        })
        .catch((err) => {
          console.error("Error en upload Excel:", err);
        });
    });
  }

  // Cargar movimientos al iniciar
  fetchMovimientos();
}


// =======================
// Escape HTML helper
// =======================
function escapeHtml(str) {
  if (str == null) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
