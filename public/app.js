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

  // Búsqueda
  if (searchBox) {
    searchBox.addEventListener("input", () => {
      fetchProductos(1, searchBox.value);
    });
  }

  // Cargar inicial
  fetchProductos();

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

  tableBody.addEventListener("click", (e) => {
    const row = e.target.closest("tr");
    if (!row) return;

    if (e.target.closest(".btn-edit")) {
      editingProductId = row.dataset.id;
      form.querySelector("#prod-nombre").value =
        row.querySelector(".p-nombre span").innerText.trim();
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

  if (!form || !selectCliente || !selectProducto) return;

  let carrito = [];
  let productos = [];

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

    const prod = productos.find((p) => p.id == prodId);
    if (!prod) return;

    // Buscar si ya existe en el carrito
    const existente = carrito.find((item) => item.id == prodId);
    if (existente) {
      existente.cantidad += cantidad;
    } else {
      carrito.push({
        id: prod.id,
        nombre: prod.nombre,
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
        <td>${item.cantidad}</td>
        <td>$${item.precio.toFixed(2)}</td>
        <td>$${subtotal.toFixed(2)}</td>
        <td><button class="btn-delete" data-idx="${idx}">❌</button></td>
      `;
      carritoBody.appendChild(tr);
    });
    totalSpan.textContent = total.toFixed(2);

    // Eliminar del carrito
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
    const payload = {
      action: "create",
      cliente_id: clienteId,
      items: carrito,
    };

    fetch("sections/ajax_ventas.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          alert("Venta registrada correctamente");
          carrito = [];
          renderCarrito();
          fetchVentas(); // recargar historial
        } else {
          alert(data.error || "Error al registrar la venta");
        }
      });
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
              <td>${v.fecha}</td>
              <td>$${parseFloat(v.total).toFixed(2)}</td>
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

  // Carga inicial del historial
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
