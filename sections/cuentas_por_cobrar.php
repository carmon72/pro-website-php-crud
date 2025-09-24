<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<h2>📑 Cuentas por Cobrar</h2>

<table border="1" cellpadding="8" width="100%">
  <thead>
    <tr>
      <th>ID Venta</th>
      <th>Cliente</th>
      <th>Fecha</th>
      <th>Total</th>
      <th>Pagado</th>
      <th>Saldo</th>
      <th>Vencimiento</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody id="cxc-table-body"></tbody>
</table>

<!-- Modal / Formulario para registrar abono -->
<div id="modal-abono" style="display:none; position:fixed; top:0; left:0; 
     width:100%; height:100%; background:rgba(0,0,0,0.6);">
  <div style="background:#fff; width:400px; margin:100px auto; padding:20px; border-radius:8px;">
    <h3>💵 Registrar Abono</h3>
    <form id="form-abono">
      <input type="hidden" id="abono-venta-id" name="venta_id">
      <label>Monto:</label>
      <input type="number" id="abono-monto" name="monto" step="0.01" min="0.01" required>
      <br><br>
      <button type="submit" class="btn-primary-registrar">Registrar</button>
      <button type="button" id="cerrar-modal" class="btn-danger">Cancelar</button>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const tbody = document.getElementById("cxc-table-body");
  const modal = document.getElementById("modal-abono");
  const formAbono = document.getElementById("form-abono");
  const inputVentaId = document.getElementById("abono-venta-id");
  const cerrarBtn = document.getElementById("cerrar-modal");

  function fetchCXC() {
    fetch("sections/ajax_cxc.php", {
      method: "POST",
      headers: {"Content-Type": "application/x-www-form-urlencoded"},
      body: "action=fetch"
    })
    .then(r => r.json())
    .then(data => {
      tbody.innerHTML = "";
      data.forEach(v => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${v.id}</td>
          <td>${v.cliente}</td>
          <td>${v.fecha}</td>
          <td>$${parseFloat(v.total).toFixed(2)}</td>
          <td>$${parseFloat(v.pagado).toFixed(2)}</td>
          <td>$${parseFloat(v.saldo).toFixed(2)}</td>
          <td>${v.fecha_vencimiento || "—"}</td>
          <td>
            ${v.saldo > 0 
              ? `<button class="btn-primary btn-abonar" data-id="${v.id}">➕ Abonar</button>` 
              : "✔ Pagada"}
          </td>
        `;
        tbody.appendChild(tr);
      });

      // Botones de abono
      document.querySelectorAll(".btn-abonar").forEach(btn => {
        btn.addEventListener("click", () => {
          inputVentaId.value = btn.dataset.id;
          modal.style.display = "block";
        });
      });
    });
  }

  // Registrar abono
  formAbono.addEventListener("submit", e => {
    e.preventDefault();
    const formData = new FormData(formAbono);
    formData.append("action", "abonar");

    fetch("sections/ajax_cxc.php", { method: "POST", body: formData })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert("Abono registrado ✅");
          modal.style.display = "none";
          fetchCXC();
        } else {
          alert(data.error || "Error al registrar abono");
        }
      });
  });

  cerrarBtn.addEventListener("click", () => modal.style.display = "none");

  fetchCXC();
});
</script>
