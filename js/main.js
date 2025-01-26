/* LOGIN */

const logregBox = document.querySelector(".logreg-box");
const loginLink = document.querySelector(".login-link");
const forgetLink = document.querySelector(".forget-link");

forgetLink?.addEventListener("click", () => {
  logregBox.classList.add("active");
});

loginLink?.addEventListener("click", () => {
  logregBox.classList.remove("active");
});

/* MOSTRAR CONTRASEÑA LOGIN */

const pass = document.getElementById("pass"),
  icon = document.querySelector(".fa-eye");

icon?.addEventListener("click", (e) => {
  if (pass.type === "password") {
    pass.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    pass.type = "password";
    icon.classList.add("fa-eye");
    icon.classList.remove("fa-eye-slash");
  }
});

/* FIN */

/* VALIDACIONES DE FORMULARIOS */

/* CAMBIO DE CONTRASEÑA */
const form_forget = document.getElementById("form_forget");

form_forget?.addEventListener("submit", (e) => {
  e.preventDefault();

  const data = new FormData(form_forget);
  const url = "./data/forget.php";

  const pass = data.get("pass_forget");

  const message = document.getElementById("error-message_forget");

  let regexPassChange =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{8,15}$/;
  let warnings = "";
  let entrar = false;

  message.innerHTML = "";

  if (!pass.match(regexPassChange)) {
    warnings = `La contraseña no es válida, el formato debe ser el siguiente:<br/>
    - Debe contener al menos 1 letra minúscula.<br/>
    - Debe contener al menos 1 letra mayúscula.<br/>
    - Debe contener al menos 1 dígito.<br/>
    - Debe contener al menos 1 carácter especial entre $, @, !, %, *, ?, o &.<br/>
    - Debe tener una longitud de entre 8 y 15 caracteres.`;
    entrar = true;
  }

  if (entrar) {
    message.innerHTML = warnings;
    return;
  }

  fetch(url, {
    method: "POST",
    body: data,
  })
    .then((res) => res.text())
    .then((dataForm) => {
      //console.log(dataForm);
      const dataObject = JSON.parse(dataForm);
      //console.log(dataObject);
      if (dataObject.success) {
        //window.location = "../index.php";
        document.getElementById("success-message_forget").innerHTML =
          dataObject.message;
      } else {
        message.innerHTML = dataObject.message;
      }
    })
    .catch((error) => {
      console.error("Error al procesar la solicitud:", error);
      message.innerHTML = "Error al procesar la solicitud";
    });
});

/* FIN */
/* REGISTRO DE USUARIO */

const submit_form_create = document.getElementById("enviar");
const form_create = document.getElementById("form_create");

form_create?.addEventListener("submit", (e) => {
  e.preventDefault();

  //const form_create = e.target;

  const data = new FormData(form_create);
  const url = "./data/data.php";
  const message = document.getElementById("error-message");

  const ci = data.get("ci"),
    nombre = data.get("name"),
    email_modal = data.get("email_modal"),
    pass_modal = data.get("pass_modal");
    //tlf_modal = data.get("nroTel");

    // Obtener datos de los vehiculos
    const numVehiculos = parseInt(document.getElementById('num_vehiculos').value);
    let vehiculosData = [];

  let warnings = "",
    entrar = false;

  let regexCI = /^(V-|E-)\d{1,2}\.\d{3}\.(\d{3})$/,
    regexName = /^[a-zA-Z\s]+$/,
    regexTvehiculo = /^[a-zA-Z]{1,5}$/,
    regexTpuesto = /^[a-zA-Z]{1,9}$/,
    regexNpuesto = /^(\d{1,3}(\s*,\s*\d{1,3})*|No aplica)$/,
    regexEmail = /^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]{2,7})+\.(com)$/,
    regexPass = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{8,15}$/;
    //regexTlf = /^\+\d{1,3}\d{10,14}$/;

  message.innerHTML = "";

  if (!ci.match(regexCI)) {
    warnings += `La cédula no es válida, el formato debe ser:</br> V- o E-xx.xxx.xxx</br>`;
    entrar = true;
  }
  if (!nombre.match(regexName)) {
    warnings += `El nombre no es válido</br>`;
    entrar = true;
  }
  if (!email_modal.match(regexEmail)) {
    warnings += `El correo no es válido</br>`;
    entrar = true;
  }
  /*if (!tlf_modal.match(regexTlf)) {
    warnings += `El formato del número de teléfono no es válido</br>
    - Debe comenzar por el código del país (+58).</br>`;
    entrar = true;
  }*/
  if (!pass_modal.match(regexPass)) {
    warnings += `La contraseña no es válida, el formato debe ser el siguiente:<br/>
    - Debe contener al menos 1 letra minúscula.<br/>
    - Debe contener al menos 1 letra mayúscula.<br/>
    - Debe contener al menos 1 dígito.<br/>
    - Debe contener al menos 1 carácter especial entre $, @, !, %, *, ?, o &.<br/>
    - Debe tener una longitud de entre 8 y 15 caracteres.`;
    entrar = true;
  }

  // Validación para cada vehículo registrado
  for (let i = 1; i <= numVehiculos; i++) {
    const tVehiculo = document.getElementById(`tVehiculo_${i}`).value;
    const tPuesto = document.getElementById(`tPuesto_${i}`).value;
    const nPuesto = document.getElementById(`nPuesto_${i}`).value;

    if (!tVehiculo.match(regexTvehiculo)) {
      warnings += `El tipo de vehiculo ${i} no es válido</br>`;
      entrar = true;
    }
    if (!tPuesto.match(regexTpuesto)) {
      warnings += `El tipo de puesto ${i} no es válido</br>`;
      entrar = true;
    }
    if (!nPuesto.match(regexNpuesto)) {
      warnings += `El número de puesto ${i} no es válido</br>`;
      entrar = true;
    }

    vehiculosData.push({
      tVehiculo: tVehiculo,
      tPuesto: tPuesto,
      nPuesto: nPuesto
    });
  }

  // Agregar los vehiculos al FormaData
  data.append("vehiculos", JSON.stringify(vehiculosData));

  if (entrar) {
    message.innerHTML = warnings;
    return;
  }

  fetch(url, {
    method: "POST",
    body: data,
  })
    .then((res) => res.text())
    .then((dataForm) => {
      const dataObject = JSON.parse(dataForm);
      
      if (dataObject.success) {
        document.getElementById("success-message").innerHTML =
          dataObject.message;
      } else {
        message.innerHTML = dataObject.message;
      }
    })
    .catch((error) => {
      console.error("Error al procesar la solicitud:", error);
      message.innerHTML = "Error al procesar la solicitud";
    });
});
/* FIN */

/* FORMULARIO DINAMICO VEHICULOS (REGISTRAR) */

document.getElementById('num_vehiculos')?.addEventListener('change', function () {
  const numVehiculos      = parseInt(this.value);
  const vehiculoRegistros = document.getElementById('vehiculo_registros');
  const vehiculoLeyenda   = document.getElementById('vehiculo_leyenda');

  vehiculoRegistros.innerHTML = '';
  vehiculoLeyenda.innerHTML = '';

  // Crear formularios según la cantidad seleccionada
  for (let i = 1; i <= numVehiculos; i++) {
    const registro = document.createElement('div');
    registro.classList.add('vehiculo_registro');

    registro.innerHTML = `
      <div class="input-box_create">
          <span class="icon"><i class="fa-solid fa-car"></i></span>
          <input type="text" id="tVehiculo_${i}" name="tVehiculo_${i}" placeholder="Carro o Moto">
          <label for="tVehiculo_${i}">Tipo de Vehículo ${i}</label>
      </div>

      <div class="input-box_create">
          <span class="icon"><i class="fa-solid fa-square-parking"></i></span>
          <select id="tPuesto_${i}" name="tPuesto_${i}">
              <option value="Fijo">Fijo</option>
              <option value="Flotante">Flotante</option>
              <option value="Moto">Moto</option>
              <option value="Otro">Otro</option>
          </select>
          <label for="tPuesto_${i}">Tipo de Puesto ${i}</label>
      </div>

      <div class="input-box_create">
          <span class="icon"><i class="fa-solid fa-list-ol"></i></span>
          <input type="text" id="nPuesto_${i}" name="nPuesto_${i}">
          <label for="nPuesto_${i}">N° de Puesto ${i}</label>
      </div>
    `;

    vehiculoRegistros.appendChild(registro);

    const tVehiculoInput  = registro.querySelector(`#tVehiculo_${i}`);
    const tPuestoSelect   = registro.querySelector(`#tPuesto_${i}`);
    const nPuestoInput    = registro.querySelector(`#nPuesto_${i}`);

    function actualizarLeyenda() {
      let leyendaHTML = '';
      for (let j = 1; j <= numVehiculos; j++) {
        const vehiculo = document.getElementById(`tVehiculo_${j}`).value || 'Sin definir';
        const puesto = document.getElementById(`tPuesto_${j}`).value || 'Sin definir';
        const numeroPuesto = document.getElementById(`nPuesto_${j}`).value || 'Sin definir';
        leyendaHTML += `${j} - ${vehiculo} - ${puesto} - ${numeroPuesto}<br>`; 
      }
      vehiculoLeyenda.innerHTML = leyendaHTML;
    }

    tPuestoSelect.addEventListener('change', function() {
      if (this.value === 'Flotante') {
        nPuestoInput.value = 'No aplica';
        nPuestoInput.disabled = true;
      } else {
        nPuestoInput.value = '';
        nPuestoInput.disabled = false;
      }
      actualizarLeyenda();
    });

    tVehiculoInput.addEventListener('input', actualizarLeyenda);
    nPuestoInput.addEventListener('input', actualizarLeyenda);
  }

});

/* FIN FORMULARIO DINAMICO VEHICULOS (REGISTRAR) */

/* FIN LOGIN */

/* SISTEMA */

/* SIDEBAR */

const btn = document.getElementById("btn");
const sidebar = document.querySelector(".sidebar");

btn.onclick = () => {
  sidebar.classList.toggle("active");
};

/* FIN SIDEBAR */

/* INPUT TASA DE CAMBIO */

document.addEventListener("DOMContentLoaded", function () {
  const inputDeuda = document.getElementById("deuda_bsd");

  inputDeuda?.addEventListener('keydown', function (e) {

    if (e.key === 'Enter') {
      const valor = inputDeuda.value.trim();

      // Validación
      if (esValido(valor)) {
        enviarAlServidor(valor);
      } else {
        console.log('El valor ingresado no es válido.');
      }

    }

  });
  
});

function esValido(valor) {
  return /^\d+(\.\d{1,2})?$/.test(valor);
}

function enviarAlServidor(valor) {
  const data = new FormData();
  data.append('deuda_bsd', valor);

  var url = "../data/tasa.php";

  fetch(url, {
    method: 'POST',
    body: data
  })
  .then(response => response.text())
  .then(result => {
    console.log('Respuesta: ', result);
  })
  .catch(error => {
    console.error('Error al enviar los datos: ', error);
  })
}

/* FIN INPUT TASA DE CAMBIO */

/* VALIDAR FORMULARIO DE PAGO */

const form_payment          = document.getElementById("form_payment");
const btn_enviar_payment    = document.getElementById("enviar_payment");
const btn_confirmar_payment = document.getElementById("confirmar_payment");
const btn_register_payment  = document.getElementById("register_payment");

const pagoSi              = document.getElementById("pagoSi");
const pagoNo              = document.getElementById("pagoNo");
const selectMesesCompleto = document.getElementById("selectMesesCompleto");
const selectMesesDeuda    = document.getElementById("selectMesesDeuda");
const tarifasContainer    = document.getElementById("tarifas-container");

const modalPayment  = document.getElementById("exampleModal");
const cerrarModal   = document.getElementById("close_modal");
const cancelarModal = document.getElementById("cancelar_modal");

let formdata

let selectedMonthNoOption   = "";
let selectedTarifasNoOption = [];

// Función para reiniciar toda la modal
function reiniciarModal () {
  form_payment.reset();

  selectMesesCompleto.style.display = "none";
  selectMesesDeuda.style.display = "none";
  tarifasContainer.style.display = "none";
  tarifasContainer.innerHTML = "";

  pagoSi.checked = false;
  pagoNo.checked = false;
}

cerrarModal?.addEventListener("click", () => {
  reiniciarModal();
});

cancelarModal?.addEventListener("click", () => {
  reiniciarModal();
});

window.addEventListener("click", (e) => {
  if (e.target === modalPayment) {
    reiniciarModal();
  }
});

// Función para actualizar la visibilidad de los selectores según el estado de los checkboxes
function actualizarSeleccion() {
  if (pagoSi.checked) {
      selectMesesCompleto.style.display = "block";
      selectMesesDeuda.style.display = "none";
      tarifasContainer.style.display = "none";
      pagoNo.checked = false; // Desmarca "No" si "Sí" está marcado
  } else if (pagoNo.checked) {
      selectMesesDeuda.style.display = "block";
      selectMesesCompleto.style.display = "none";
      tarifasContainer.style.display = "none"
      pagoSi.checked = false; // Desmarca "Sí" si "No" está marcado
  } else {
      selectMesesCompleto.style.display = "none";
      selectMesesDeuda.style.display = "none";
      tarifasContainer.style.display = "none"
  }
}

pagoSi?.addEventListener("change", actualizarSeleccion);
pagoNo?.addEventListener("change", actualizarSeleccion);

// Función para cargar tarifas al seleccionar el mes de la opcion "No" con Fetch
function cargarTarifas(mes, id) {
  if (!mes || !id) {
    tarifasContainer.innerHTML = "";
    tarifasContainer.style.display = "none";
    return;
  }

  fetch(`../data/get_tarifa_detalle.php?mes=${mes}&id=${id}`)
    .then(response => {
      if (!response.ok) {
        throw new Error("Error en la respuesta de la solicitud");
      }
      return response.text();
    })
    .then(html => {
      //console.log("Respuesta del PHP:", html);

      if (tarifasContainer) {
        tarifasContainer.innerHTML = html;

        // Muestra el contenedor solo si hay contenido en HTML
        tarifasContainer.style.display = html.trim() !== "" ? "block" : "none";
      } else {
        console.error("tarifasContainer no está definido en el DOM.");
      }

    })
    .catch(error => console.error("Error al cargar las tarifas:", error));
}

selectMesesDeuda?.addEventListener("change", (e) => {
  const [mes, id] = e.target.value.split(" - ");
  selectedMonthNoOption = `${mes} - ${id}`;
  cargarTarifas(mes.trim(), id.trim());
});

// Función para enviar los datos del formulario a la ventana modal de verificación
function openVerificationModal() {
  var name_payment    = document.getElementById("name_payment").value;
  var date            = document.getElementById("date").value;
  var dateParts       = date.split("-");
  var formattedDate   = dateParts[2] + "-" + dateParts[1] + "-" + dateParts[0];
  var bank_select     = document.getElementById("bank_select").value;
  var tPuesto_select  = document.getElementById("tPuesto").value;
  var tPago_select    = document.getElementById("tPago").value;
  var tPago_text      =  tPago_select == "0" ? "Pago Móvil" :
                          tPago_select == "1" ? "Transferencia" :
                          tPago_select == "2" ? "Efectivo" :
                          "Desconocido";
  var nPuesto         = document.getElementById("nPuesto").value;
  var money           = document.getElementById("money").value;
  var moneyBs         = document.getElementById("money_bs").value;
  var reference       = document.getElementById("ref").value;
  const fileInput     = document.getElementById("capture");
  const file          = fileInput.files[0];

  var selectedMonthsText;
  var tarifasArray = [];

  if (pagoSi.checked) {
    const selectedMonthsAndYears = Array.from(
      document.getElementById("meses_dashboard").selectedOptions
    ).map((option) => option.value);
    selectedMonthsText = selectedMonthsAndYears.join(", ");
  } else if (pagoNo.checked) {
    // Obtiene el select que contiene las tarifas desde tarifasContainer
    const tarifasSelect = tarifasContainer.querySelector('#tarifas');
    const selectedTarifas = Array.from(tarifasSelect.selectedOptions).map(opt => opt.textContent);

    tarifasArray = selectedTarifas.map(tarifa => tarifa.trim());
    selectedMonthsText = `${selectedMonthNoOption.split(" - ")[0]} - Tarifa(s): ${selectedTarifas.join(", ")}`;
  } else {
    selectedMonthsText = "No seleccionado";
  }

  let imageContent;
  if (file) {
    const imageURL = URL.createObjectURL(file);
    imageContent = `<img src="${imageURL}" alt="Captura de pantalla" class="image-modal">`;
  } else {
    imageContent = "No se ha cargado ninguna imagen.";
  }

  var modalBody = document.querySelector("#ModalPayment .modal-body");
  modalBody.innerHTML = `
    <div class="datos"><span class="titulo">Nombre Completo:</span></div> <div class="info"><span class="dato">${name_payment}</span></div>
    <div class="datos"><span class="titulo">Fecha del pago:</span></div> <div class="info"><span class="dato">${formattedDate}</span></div>
    <div class="datos"><span class="titulo">Banco Emisor:</span></div> <div class="info"><span class="dato">${bank_select}</span></div>
    <div class="datos"><span class="titulo">Tipo de puesto:</span></div> <div class="info"><span class="dato">${tPuesto_select}</span></div>
    <div class="datos"><span class="titulo">Forma de pago:</span></div> <div class="info"><span class="dato">${tPago_text}</span></div>
    <div class="datos"><span class="titulo">Número de puesto:</span></div> <div class="info"><span class="dato">${nPuesto}</span></div>
    <div class="datos"><span class="titulo">Meses pagados:</span></div> <div class="info"><span class="dato">${selectedMonthsText}</span></div>
    <div class="datos"><span class="titulo">Monto dólares:</span></div> <div class="info"><span class="dato">${money}</span></div>
    <div class="datos"><span class="titulo">Monto bolívares:</span></div> <div class="info"><span class="dato">${moneyBs}</span></div>
    <div class="datos"><span class="titulo">Referencia:</span></div> <div class="info"><span class="dato">${reference}</span></div>
    <div class="datos"><span class="titulo">Imagen:</span></div> <div class="info">${imageContent}</div>`;

  tarifasArray.forEach((tarifa, index) => {
    formdata.append(`tarifas[${index}]`, tarifa);
  })

  //console.log(selectedMonthsText);
}

// Función para enviar el formulario al servidor
function registerPayment() {
  const url = "../data/payment.php";
  fetch(url, {
    method: "POST",
    body: formdata,
  })
    .then((response) => response.text())
    .then((text) => {
      // Intenta parsear el texto como JSON
      try {
        const dataObject = JSON.parse(text);
        //console.log(dataObject);
        // Actualizar los elementos de la página con los datos recibidos
        if (dataObject.saldo !== undefined) {
          document.getElementById("balance").textContent =
            "$ " + dataObject.saldo;
        }
        /*if (dataObject.saldoBs !== undefined) {
          document.getElementById("balanceBs").textContent =
            "Bs " + dataObject.saldoBs;
        }*/
        if (dataObject.saldo !== undefined) {
          document.getElementById("money_table").textContent = dataObject.saldo;
        }
        if (dataObject.cnt_meses !== undefined) {
          document.getElementById("cnt_meses").textContent =
            dataObject.cnt_meses;
        }
        if (dataObject.cnt_meses !== undefined) {
          document.getElementById("meses").textContent = dataObject.cnt_meses;
        }
        if (dataObject.meses !== undefined) {
          document.getElementById("meses_table").textContent = dataObject.meses;
        }

        // Mostrar la alerta de éxito
        const alertContainer = document.getElementById("alert-container");
        const msgSuccess = dataObject.message
        alertContainer.innerHTML =
          '<div class="alert alert-success" role="alert">' + msgSuccess + '</div>';

        // Ocultar el modal después de confirmar el pago
        const modalElement = document.querySelector("#ModalPayment");
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        modalInstance.hide();

        // Ocultar la alerta después de unos segundos
        setTimeout(() => {
          alertContainer.innerHTML = '';
        }, 10000);
        
      } catch (error) {
        // Si la respuesta no es JSON, mostrar el error HTML en la consola
        if (text.startsWith('<')) {
          console.error("Error HTML recibido:", text);
        } else {
          console.error("Error al parsear JSON:", error);
          console.error("Texto recibido:", text);
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

// Verificar si hay errores para abrir la segunda ventana modal
btn_enviar_payment?.addEventListener("click", (e) => {
  e.preventDefault();
  const form_payment = document.getElementById("form_payment");
  if (form_payment) {
    formdata = new FormData(form_payment);
    console.log([...formdata])
    const hasErrors = validateData(formdata);
    if (hasErrors) {
      // Si hay errores, deshabilitar el botón "confirmar_payment"
      btn_confirmar_payment.setAttribute("disabled", "true");
    } else {
      // Si no hay errores, habilitar el botón "confirmar_payment" y abrir la segunda ventana modal
      btn_confirmar_payment.removeAttribute("disabled");
      openVerificationModal();
    }
  } else {
    console.error("Formulario con id 'form_payment' no encontrado.");
  }
});

// Enviar formulario desde la modal de verificación
btn_register_payment?.addEventListener("click", (e) => {
  e.preventDefault();

  if (form_payment) {
    registerPayment();
  } else {
    console.error("Formulario con id 'form_payment' no encontrado.");
  }
});

// Función para validar los datos
function validateData(data) {
  let warnings = "";
  let entrar = false;

  const userSaldo = document.getElementById('user-saldo').value;
  const userSaldoBs = document.getElementById('user-saldoBs').value;

  const name_payment = data.get("name_payment");
  const img_payment = document.getElementById("capture").files[0];
  const fecha_payment = data.get("date");
  const bank_payment = data.get("bank_select");
  const nPuesto_payment = data.get("nPuesto");
  const meses_payment = Array.from(data.getAll("meses_dashboard"));

  // Eliminar meses_dashboard antes de agregar los valores
  data.delete("meses_dashboard");
  meses_payment.forEach((mes, index) => {
    data.append(`meses_dashboard[${index}]`, mes);
  });

  const money_payment = data.get("money");
  const moneyBs_payment = data.get("money_bs");
  const reference_payment = data.get("ref");

  const regexName = /^[a-zA-Z\s]+$/;
  const allowedExtensions = /(\.jpg|\.jpeg)$/i;

  // Verificar cada campo y agregar mensajes de advertencia
  if (!regexName.test(name_payment)) {
    warnings += `- El nombre no es válido, solo debe contener letras mayúsculas y minúsculas.<br>`;
    entrar = true;
  }
  if (img_payment && !allowedExtensions.test(img_payment.name)) {
    warnings += `- El tipo de imagen no es válido, solo se permite imágenes jpg o jpeg.<br>`;
    entrar = true;
  }
  if (fecha_payment === "") {
    warnings += `- Debe colocar una fecha.<br>`;
    entrar = true;
  }
  if (bank_payment === "") {
    warnings += `- Debe seleccionar un banco.<br>`;
    entrar = true;
  }
  if (nPuesto_payment === "") {
    warnings += `- Debe colocar el/los números de puesto.<br>`;
    entrar = true;
  }
  /*if (meses_payment.length === 0) {
    warnings += `- Debe seleccionar los meses que pagó.<br>`;
    entrar = true;
  }*/
  if (money_payment === "") {
    warnings += `- Debe colocar el monto que pagó.<br>`;
    entrar = true;
  }
  if (moneyBs_payment === "") {
    warnings += `- Debe colocar el monto que pagó.<br>`;
    entrar = true;
  }
  if (userSaldo !== null && userSaldo !== "") {
    if (parseFloat(money_payment) > parseFloat(userSaldo)) {
        warnings += `- El monto a pagar no puede ser mayor que el saldo a pagar ($${userSaldo}).<br>`;
        entrar = true;
    }
  } else {
      warnings += `- No se pudo obtener el saldo del usuario.<br>`;
      entrar = true;
  }
  if (userSaldoBs !== null && userSaldoBs !== "") {
    if (parseFloat(moneyBs_payment) > parseFloat(userSaldoBs)) {
        warnings += `- El monto a pagar no puede ser mayor que el saldo a pagar ($${userSaldoBs}).<br>`;
        entrar = true;
    }
  } else {
      warnings += `- No se pudo obtener el saldo del usuario.<br>`;
      entrar = true;
  }
  if (reference_payment === "") {
    warnings += `- Debe colocar el número de referencia.<br>`;
    entrar = true;
  }

  // Actualizar el mensaje de error
  const message = document.getElementById("error-message");
  if (message) {
    message.innerHTML = warnings;
  } else {
    console.error("Elemento con id 'error-message' no encontrado.");
  }

  return entrar;
}

/* FIN DE LA VALIDACION FORMULARIO DE PAGO */

/* FORMULARIO DE REGISTRO DE CUENTAS POR COBRAR */

const searchInput = document.getElementById("search");
const resultsDiv = document.getElementById("results");
const user_form = document.getElementById("user_form");
const user_form_reg = document.getElementById("user_regcxc");

const name_input = document.getElementById("name");
const ci_input = document.getElementById("ci");
const email_input = document.getElementById("email");
const id_input = document.getElementById("id");

document.addEventListener("DOMContentLoaded", function () {
  const selectMeses = document.getElementById("months");
  const tarifaContainer = document.getElementById("tarifa");
  const totalInput = document.getElementById("total");
  const selectedYearsContainer = document.getElementById("selectedYears");

  let vehiculosData = [];
  let mesesSeleccionados = 0;
  let selectedUserId = null;
  let mesesPorAnio = {};
  let tarifasSeleccionadas = {};
  let detallesCxc = [];

  // Obtener la información de los vehículos y tarifas
  function cargarVehiculosYTarifas(user_id) {
    if (user_id === null) {
      console.error("User ID is not set.");
      return;
    }

    fetch(`../data/get_vehiculos_tarifas.php?user_id=${user_id}`)
      .then(response => response.json())
      .then(data => {
          //console.log("Datos recibidos:", data);
          vehiculosData = data;
          mostrarTarifas();
      })
      .catch(error => console.error("Error al cargar tarifas:", error));
  }

  // Mostrar las tarifas en la leyenda
  function mostrarTarifas() {
    //console.log("Mostrando tarifas:", vehiculosData); 
    tarifaContainer.innerHTML = "";

    tarifasSeleccionadas = vehiculosData.map(vehiculo => ({
      tarifa: vehiculo.tarifa,
      tipoPuesto: vehiculo.tipo_puesto,
      tarifa_id: vehiculo.tarifa_id
    }));

    vehiculosData.forEach((vehiculo, index) => {
      tarifaContainer.innerHTML += `
        <p>${index + 1} - ${vehiculo.vehiculo} - ${vehiculo.tipo_puesto} - ${vehiculo.tarifa}</p>
      `;
    });
  }

  // Calcular el total con base en los meses seleccionados y las tarifas
  function calcularTotal() {
    let total = 0;
    mesesSeleccionados = 0;

    Array.from(selectedYearsContainer.querySelectorAll(".optiones_years")).forEach(optiones_years => {
      const radioNo = optiones_years.querySelector('input[type="radio"][value="no"]');

      const radioSi = optiones_years.querySelector('input[type="radio"][value="yes"]');

      if (radioNo.checked) {
        const selectMonths = optiones_years.querySelector('select[name="months"]');
        mesesSeleccionados += Array.from(selectMonths.selectedOptions).length;
      } else if (radioSi.checked) {
        mesesSeleccionados += 12;
      }
    });

    vehiculosData.forEach(vehiculo => {
      const tarifa = parseFloat(vehiculo.tarifa);
      total += tarifa * mesesSeleccionados;
    });

    totalInput.value = total;
  }

  // Actualizar los años seleccionados cuando se cambia la selección de meses
  function actualizarAñosSeleccionados() {
    selectedYearsContainer.innerHTML = "";

    Array.from(selectMeses.selectedOptions).forEach((option, index) => {
      const year = option.text;
      selectedYearsContainer.innerHTML += `
        <div class="optiones_years">
          <div class="options">
              <label>¿El usuario debe el año ${year} completo?</label>
              <input type="radio" name="debt_${year}_${index}" id="yes_${index}" value="yes">
              <label for="yes_${index}">Sí</label>
              <input type="radio" name="debt_${year}_${index}" id="no_${index}" value="no">
              <label for="no_${index}">No</label>
          </div>
          <select name="months" id="calendar_${index}" class="user_months" style="display: none;" multiple>
              <option value="1">Enero</option>
              <option value="2">Febrero</option>
              <option value="3">Marzo</option>
              <option value="4">Abril</option>
              <option value="5">Mayo</option>
              <option value="6">Junio</option>
              <option value="7">Julio</option>
              <option value="8">Agosto</option>
              <option value="9">Septiembre</option>
              <option value="10">Octubre</option>
              <option value="11">Noviembre</option>
              <option value="12">Diciembre</option>
          </select>
        </div>
      `;
    });

    Array.from(selectedYearsContainer.querySelectorAll('input[type="radio"]')).forEach(input => {
      input.addEventListener("click", function () {
        if (this.value === "no") {
          showCalendar(this);
        } else {
          hideCalendar(this);
        }
        calcularTotal();
      });
    });

    Array.from(selectedYearsContainer.querySelectorAll('select[name="months"]')).forEach(select => {
      select.addEventListener("change", calcularTotal); // Recalcula el total al seleccionar meses
    });
  }

  function showCalendar(input) {
      const calendar = document.getElementById(`calendar_${input.id.split("_")[1]}`);
      calendar.style.display = "block";
  }

  function hideCalendar(input) {
      const calendar = document.getElementById(`calendar_${input.id.split("_")[1]}`);
      calendar.style.display = "none";
  }

  selectMeses?.addEventListener("change", function () {
      actualizarAñosSeleccionados();
      calcularTotal();
  });

  document.getElementById("form_cxc")?.addEventListener("submit", function (e) {
    e.preventDefault();

    mesesPorAnio = {};
    detallesCxc = [];

    Array.from(selectedYearsContainer.querySelectorAll(".optiones_years")).forEach(optiones_years => {
      const radioNo = optiones_years.querySelector('input[type="radio"][value="no"]');
      const radioSi = optiones_years.querySelector('input[type="radio"][value="yes"]');
      const year = optiones_years.querySelector("label").textContent.split(" ")[5];

      if (radioNo.checked) {
        const selectMonths = optiones_years.querySelector('select[name="months"]');
        const selectedOptions = Array.from(selectMonths.selectedOptions).map(option => option.text);

        if (!mesesPorAnio[year]) {
          mesesPorAnio[year] = selectedOptions.join(", ");
        } else {
          mesesPorAnio[year] += ", " + selectedOptions.join(", ");
        }

        selectedOptions.forEach(mes => {
          vehiculosData.forEach(vehiculo => {
            detallesCxc.push({
              anio: year,
              mes,
              vehiculo: vehiculo.vehiculo,
              tarifa_id: vehiculo.tarifa_id,
              monto: vehiculo.tarifa
            });
          });
        });

      } else if (radioSi.checked) {
        const yearMonths = [
          "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
          "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];
        mesesPorAnio[year] = yearMonths.join(", ");

        yearMonths.forEach(mes => {
          vehiculosData.forEach(vehiculo => {
            detallesCxc.push({
              anio: year,
              mes,
              vehiculo: vehiculo.vehiculo,
              tarifa_id: vehiculo.tarifa_id,
              monto: vehiculo.tarifa
            });
          });
        });
      }
    });
    //console.log("Datos vehiculos: ", vehiculosData);
    //console.log("DetallesCxc: ", detallesCxc);
    if (selectedUserId !== null) {
      
      const datos = {
        id: selectedUserId,
        meses: mesesPorAnio,
        totalMeses: mesesSeleccionados,
        total: totalInput.value,
        tarifas: tarifasSeleccionadas,
        detalles: detallesCxc
      };
      enviarDatos(datos);
    } else {
      console.error("No se ha seleccionado ningún usuario.");
    }
  });

  /* BARRA DE BUSQUEDA DE USUARIO DE CUENTAS POR COBRAR */
  searchInput?.addEventListener("keyup", function () {
    //console.log("Keyup event triggered");
    let filter = this.value.toUpperCase();
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (this.readyState === 4 && this.status === 200) {
        let searchResults = JSON.parse(this.responseText);
        resultsDiv.innerHTML = "";
  
        // Verificar si hay una coincidencia exacta
        let exactMatch = searchResults.some(result => result.nombre_completo.toUpperCase() === filter);
  
        // Limitar los resultados a 5 si no es una coincidencia exacta
        let maxResults = exactMatch ? searchResults.length : Math.min(5, searchResults.length);
  
        for (let i = 0; i < maxResults; i++) {
          let div = document.createElement("div");
          div.textContent = searchResults[i].nombre_completo;
          div.addEventListener("click", function () {
            //console.log("Fetching user with ID:", searchResults[i].id);
            selectedUserId = searchResults[i].id; // Guardar el ID del usuario seleccionado
            //console.log(selectedUserId);
            fetch(`../data/regcxc.php?user_id=${searchResults[i].id}`)
              .then((response) => response.json())
              .then((data) => {
                if (data.error) {
                  alert(data.error);
                } else {
                  user_form.style.display = "grid";
                  if (user_form_reg) {
                    user_form_reg.style.display = "grid";
                  }
                  id_input.value = data.id;
                  name_input.value = data.nombre_completo;
                  ci_input.value = data.cedula;
                  email_input.value = data.correo;
  
                }
              })
              .catch((error) => console.error("Error: ", error));
              cargarVehiculosYTarifas(selectedUserId);
          });
          resultsDiv.appendChild(div);
        }
      }
    };
    xhr.open("GET", "../data/search.php?query=" + filter, true);
    xhr.send();
  });
  /* FIN DE LA BARRA DE BUSQUEDA DE USUARIO DE CUENTAS POR COBRAR */

  function enviarDatos(datos) {
    fetch("../data/cxc.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(datos),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error ${response.status}`);
        }
        return response.text();
      })
      .then((data) => {
        console.log(data);
        // Mostrar la alerta de éxito
        const jsonData = JSON.parse(data);

        const alertContainerCxc = document.getElementById("alert-container_cxc");
        const msgSuccessCxc = jsonData.message
        alertContainerCxc.innerHTML =
          '<div class="alert alert-success" role="alert">' + msgSuccessCxc + '</div>';

        // Ocultar la alerta después de unos segundos
        setTimeout(() => {
          alertContainerCxc.innerHTML = '';
        }, 10000);

      })
      .catch((error) => {
        console.error("Error:", error);
      });
  }
});

/* FIN DEL FORMULARIO DE REGISTRO DE CUENTAS POR COBRAR */

/* FORMULARIO PARA INFORMACION DE PROBLEMAS */

const btn_report = document.getElementById("registrarBtn_report");

btn_report?.addEventListener("click", (e) => {
  e.preventDefault();

  const form_report = document.getElementById("form_report");
  let formData = new FormData(form_report);

  const url = "../data/report.php";

  fetch(url, {
    method: 'POST',
    body: formData
  })
    .then(response => response.text())
    .then((data) => {

      try{
        const dataObject = JSON.parse(data);

        // Mostrar la alerta de éxito
        const alertContainerReport = document.getElementById("alert-container_report");
        const msgSuccessReport = dataObject.message
        alertContainerReport.innerHTML =
          '<div class="alert alert-success" role="alert">' + msgSuccessReport + '</div>';

          // Ocultar la alerta después de unos segundos
          setTimeout(() => {
            alertContainerReport.innerHTML = '';
          }, 10000);

      } catch (error) {
        if (text.startsWith('<')) {
          console.error("Error HTML recibido:", text);
        } else {
          console.error("Error al parsear JSON:", error);
          console.error("Texto recibido:", text);
        }
      }

    })
    .catch(error => {
      console.error('Error:', error);
      alert('Hubo un error al enviar el formulario');
    })
});

/* FIN FORMULARIO PARA INFORMACION DE PROBLEMAS */

/* CONFIGURACIÓN DE USUARIO */
document.addEventListener('DOMContentLoaded', function () {

  const modificarBtn    = document.getElementById('btn_config');
  const form_config     = document.getElementById('form_config');
  const alertContainer  = document.getElementById('alert-container_config');

  let originalValues = {};

  // Capturar los valores originales al cargar la página
  function captureOriginalValues() {
    let ciConfig = document.getElementById('ci_config');
    let nameConfig = document.getElementById('name_config');
    let emailConfig = document.getElementById('email_config');
  
    originalValues = {
      ci: ciConfig ? ciConfig.value : '',
      nombre: nameConfig ? nameConfig.value : '',
      email: emailConfig ? emailConfig.value : ''
    };
  }

  // Capturar los valores originales al cargar la página
  captureOriginalValues();

  modificarBtn?.addEventListener('click', function () {
    const inputs = document.querySelectorAll('#form_config input');
    inputs.forEach(input => {
        input.removeAttribute('disabled');
    });
  });

  form_config?.addEventListener('submit', function (e) {
    e.preventDefault();

    let warnings = "";
    let entrar = false;

    const ci = document.getElementById('ci_config').value;
    const nombre = document.getElementById('name_config').value;
    const email = document.getElementById('email_config').value;
    const pass = document.getElementById('pass_config').value;

    const regexCI = /^(V-|E-)\d{1,2}\.\d{3}\.(\d{3})$/;
    const regexName = /^[a-zA-Z\s]+$/;
    const regexEmail = /^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]{2,7})+\.(com)$/;
    const regexPass = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{8,15}$/;

    alertContainer.innerHTML = "";

    if (!ci.match(regexCI)) {
        warnings += `La cédula no es válida, el formato debe ser:</br> V- o E-xx.xxx.xxx</br>`;
        entrar = true;
    }
    if (!nombre.match(regexName)) {
        warnings += `El nombre no es válido</br>`;
        entrar = true;
    }
    if (!email.match(regexEmail)) {
        warnings += `El correo no es válido</br>`;
        entrar = true;
    }
    if (pass && !pass.match(regexPass)) {
        warnings += `La contraseña no es válida, el formato debe ser el siguiente:<br/>
        - Debe contener al menos 1 letra minúscula.<br/>
        - Debe contener al menos 1 letra mayúscula.<br/>
        - Debe contener al menos 1 dígito.<br/>
        - Debe contener al menos 1 carácter especial entre $, @, !, %, *, ?, o &.<br/>
        - Debe tener una longitud de entre 8 y 15 caracteres.`;
        entrar = true;
    }

    if (entrar) {
      alertContainer.innerHTML = `<div class="alert alert-warning">${warnings}</div>`;
    } else {
      const formData = new FormData(form_config);

      // Agregar valores de los campos que se han cambiado
      if (ci !== originalValues.ci) formData.append('ci', ci);
      if (nombre !== originalValues.nombre) formData.append('name', nombre);
      if (email !== originalValues.email) formData.append('email', email);
      if (pass) formData.append('pass', pass);

      fetch('../data/update_user.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        const cleanedData = data.trim();

        try {
        
          const dataObject = JSON.parse(cleanedData);

          if (dataObject.success) {
            alertContainer.innerHTML = `<div class="alert alert-success">${dataObject.message}</div>`;
          } else {
            alertContainer.innerHTML = `<div class="alert alert-danger">${dataObject.message}</div>`;
          }
        } catch (error) {
          alertContainer.innerHTML = `<div class="alert alert-danger">Error en la respuesta del servidor: ${error.message}</div>`;
          console.error('Error:', error);
        }

      })
      .catch(error => {
        alertContainer.innerHTML = `<div class="alert alert-danger">Ocurrió un error al enviar los datos.</div>`;
        console.error('Error:', error);
      });
    }

  });
});

/* FIN CONFIGURACIÓN DE USUARIO */

/* FUNCIONALIDAD PARA CAMBIAR LA TABLA DE REGISTRO DE PAGO */

document.addEventListener("DOMContentLoaded", function() {
  // Verifica si la URL contiene el hash de la modal
  if (window.location.hash === '#Modal_reg_payment') {
      // Abre la ventana modal
      var myModal = new bootstrap.Modal(document.getElementById('Modal_reg_payment'), {
          backdrop: 'static',
          keyboard: false
      });
      myModal.show();
  }
});

/* FIN DE LA FUNCIONALIDAD PARA CAMBIAR LA TABLA DE REGISTRO DE PAGO */

/* MODAL DE MODIFICACIÓN DE USUARIO */

document.addEventListener('DOMContentLoaded', function() {
  // Para el modal de eliminar
  const deleteButtons = document.querySelectorAll('.deleteBtn');
  const deleteForm = document.getElementById('deleteForm');
  const userIdInput = document.getElementById('userIdToDelete');
  const userNameSpan = document.getElementById('userName');
  const alertContainerUsers = document.getElementById('alert-container_users');

  deleteButtons.forEach(button => {
      button.addEventListener('click', function() {
          const userId = this.getAttribute('data-id');
          const userName = this.getAttribute('data-name');
          userIdInput.value = userId;
          userNameSpan.textContent = userName;
      });
  });

  deleteForm?.addEventListener('submit', function(event) {
      event.preventDefault();
      const userId = userIdInput.value;
      fetch('../data/delete_user.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
              'user_id': userId
          })
      })
      .then(response => response.text())
      .then(data => {
          // Oculta el modal
          const deleteModal = bootstrap.Modal.getInstance(document.getElementById('ModalDelete'));
          deleteModal.hide();

          const dataObject = JSON.parse(data);

          if (dataObject.success === true) {
            alertContainerUsers.innerHTML = `<div class="alert alert-success">${dataObject.message_success}</div>`;
          } else {
            alertContainerUsers.innerHTML = `<div class="alert alert-danger">${dataObject.message_denied}</div>`;
          }

          // Recarga la página para actualizar la tabla
          /*setTimeout(() => {
            location.reload();
          }, 5000 );*/
      })
      .catch(error => console.error('Error:', error));
  });

  // Para el modal de editar
  const editButtons = document.querySelectorAll('.editBtn');
  const editUserForm = document.getElementById('editUserForm');
  const userIdInputEdit = document.getElementById('userId');
  const userNameToEditSpan = document.getElementById('userNameToEdit');

  // Para agregar otro vehículo
  let vehicleCount = 0;

  editButtons.forEach(button => {
    button.addEventListener('click', function() {
      const userId = this.getAttribute('data-id');
      
      // Obtener los datos del usuario a editar
      fetch(`../data/get_user.php?id=${userId}`, {
          method: 'GET',
          headers: {
              'Content-Type': 'application/json'
          },
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
            // Rellenar el formulario del modal con los datos del usuario
            userIdInputEdit.value = data.user.id;
            document.getElementById('usuario').value = data.user.usuario;
            document.getElementById('nombreCompleto').value = data.user.nombre_completo;
            document.getElementById('correo').value = data.user.correo;
            userNameToEditSpan.textContent = data.user.nombre_completo;

            const vehiculosContainer = document.getElementById('vehiculosContainer');
            vehiculosContainer.innerHTML = "";

            // Reiniciar vehicleCount y asignarlo en base a los vehículos existentes
            vehicleCount = data.user.vehiculos ? data.user.vehiculos.length : 0;

            // Agregar campos de vehículos dinámicamente si existen
            if (Array.isArray(data.user.vehiculos) && data.user.vehiculos.length > 0) {
              data.user.vehiculos.forEach((vehiculo, index) => {
                //console.log(`Vehículo ${index + 1} ID:`, vehiculo.vehiculo_id);
                const vehiculoDiv = document.createElement('div');
                vehiculoDiv.className = 'mb-3';
                
                vehiculoDiv.innerHTML = `
                  <h6>Vehículo ${index + 1}</h6>
                  <button type="button" class="btn btn-danger btn-sm deleteVehicleBtn" data-id="${vehiculo.vehiculo_id}">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                  <input type="hidden" name="vehiculo_${index}_id" value="${vehiculo.vehiculo_id}">
                  <div class="row mb-2">
                    <div class="col">
                      <label for="vehiculo${index}" class="form-label">Tipo de Vehículo</label>
                      <input type="text" class="form-control" id="vehiculo${index}" value="${vehiculo.tipo_vehiculo}" name="vehiculo_${index}_tipo">
                    </div>
                    ${vehiculo.puestos.map((puesto, puestoIndex) => `
                      <div class="col">
                        <input type="hidden" name="vehiculo_${index}_puesto_${puestoIndex}_id" value="${puesto.puesto_id}">
                        <label for="tipoPuesto${index}_${puestoIndex}" class="form-label">Tipo de Puesto</label>
                        <input type="text" class="form-control tipo-puesto" id="tipoPuesto${index}_${puestoIndex}" value="${puesto.tipo_puesto}" name="vehiculo_${index}_puesto_${puestoIndex}_tipo">
                      </div>
                      <div class="col">
                        <label for="nPuesto${index}_${puestoIndex}" class="form-label">Número de Puesto</label>
                        <input type="text" class="form-control n-puesto" id="nPuesto${index}_${puestoIndex}" value="${puesto.n_puesto}" name="vehiculo_${index}_puesto_${puestoIndex}_numero">
                      </div>
                    `).join('')}
                  </div>
                `;
                
                vehiculosContainer.appendChild(vehiculoDiv);
              });

              // Agregar listeners para manejar el cambio en el tipo de puesto
              document.querySelectorAll('.tipo-puesto').forEach((input, puestoIndex) => {
                input.addEventListener('input', function() {
                    const nPuestoInputId = `nPuesto${Math.floor(puestoIndex / 2)}_${puestoIndex % 2}`;
                    const nPuestoInput = document.getElementById(nPuestoInputId);
            
                    if (nPuestoInput) {
                        if (this.value.toLowerCase() === 'flotante') {
                            nPuestoInput.value = 'No aplica';
                            nPuestoInput.setAttribute('readonly', 'readonly');
                        } else {
                            nPuestoInput.value = '';
                            nPuestoInput.removeAttribute('readonly');
                        }
                    } else {
                        console.warn(`Elemento con ID ${nPuestoInputId} no encontrado.`);
                    }
                });
            });

            } else {
                vehiculosContainer.innerHTML = '<p>No se encontraron vehículos para este usuario.</p>';
            }

          } else {
              alert('Error al cargar los datos del usuario.');
          }
      })
      .catch(error => console.error('Error:', error));
    });
  });

  // Eliminar vehículo
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('deleteVehicleBtn') || e.target.closest('.deleteVehicleBtn')) {
      const deleteButton = e.target.closest('.deleteVehicleBtn');
      const vehiculoId = deleteButton.getAttribute('data-id');
        
      // Confirmación antes de eliminar
      if (confirm('¿Estás seguro de que deseas eliminar este vehículo?')) {
          fetch('../data/delete_vehicle.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: new URLSearchParams({
                  'vehiculo_id': vehiculoId
              })
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert(data.message);
                  // Recargar la página para actualizar la lista de vehículos
                  location.reload();
              } else {
                  alert('Error al eliminar el vehículo.');
              }
          })
          .catch(error => console.error('Error:', error));
      }

    }
  });

  // Manejar el envío del formulario de edición
  document.getElementById('saveChangesBtn')?.addEventListener('click', function() {
    const formData = new FormData(editUserForm);

    const formDataObject = {};
    formData.forEach((value, key) => {
        formDataObject[key] = value;
    });
    //console.log('Datos del FormData:', formDataObject);

    fetch('../data/update_user_admin.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        //console.log('Vehículos obtenidos:', data.vehicles);
        const editModal = bootstrap.Modal.getInstance(document.getElementById('ModalEdit'));
        editModal.hide();
        alertContainerUsers.innerHTML = `<div class="alert alert-success">${data.message_success}</div>`;
      } else {
        alert('Error al actualizar los datos del usuario: ' + (result.message_denied || 'Error desconocido.'));
      }
  
      /*if (data.debugInfo) {
        console.log('Detalles de la actualización:', data.debugInfo);
      }*/
    })
    .catch(error => {
      console.error('Error en la solicitud:', error);
      alert('Hubo un error al enviar la solicitud: ', error);
    });
  });

  // Para agregar otro vehículo
  const addVehicleBtn = document.getElementById('addVehicleBtn');

  addVehicleBtn?.addEventListener('click', function() {
    vehicleCount++;

    const vehiculosContainer = document.getElementById('vehiculosContainer');
    const vehiculoDiv = document.createElement('div');
    vehiculoDiv.className = 'mb-3';

    vehiculoDiv.innerHTML = `
      <h6>Nuevo Vehículo ${vehicleCount}</h6>
      <div class="row mb-2">
        <div class="col">
          <label for="newVehiculo${vehicleCount}" class="form-label">Tipo de Vehículo</label>
          <input type="text" class="form-control" id="newVehiculo${vehicleCount}" name="vehiculo_${vehicleCount}_tipo_nuevo" required>
        </div>
        <div class="col">
          <label for="newTipoPuesto${vehicleCount}_1" class="form-label">Tipo de Puesto</label>
          <input type="text" class="form-control tipo-puesto" id="newTipoPuesto${vehicleCount}_1" name="vehiculo_${vehicleCount}_puesto_1_tipo_nuevo" required>
        </div>
        <div class="col">
          <label for="newNPuesto${vehicleCount}_1" class="form-label">Número de Puesto</label>
          <input type="text" class="form-control n-puesto" id="newNPuesto${vehicleCount}_1" name="vehiculo_${vehicleCount}_puesto_1_numero_nuevo" required>
        </div>
      </div>
    `;

    vehiculosContainer.appendChild(vehiculoDiv);

    // Agregar la funcionalidad de flotante al nuevo input
    const tipoPuestoInput = vehiculoDiv.querySelector(`#newTipoPuesto${vehicleCount}_1`);
    const nPuestoInput = vehiculoDiv.querySelector(`#newNPuesto${vehicleCount}_1`);

    tipoPuestoInput.addEventListener('input', function() {
      if (this.value.toLowerCase() === 'flotante') {
        nPuestoInput.value = 'No aplica';
        nPuestoInput.setAttribute('readonly', 'readonly');
      } else {
        nPuestoInput.value = '';
        nPuestoInput.removeAttribute('readonly');
      }
    });
  });
});

/* FIN MODAL DE MODIFICACIÓN DE USUARIO */

/* MODIFICAR TARIFAS */

const formTarifas = document.getElementById('form_configTarifas');

formTarifas?.addEventListener('submit', function(e) {
  e.preventDefault();

  const alertContainerTarifas = document.getElementById('alert-container_configTarifas');

  let form = e.target;
  let formData = new FormData(form);
  
  const formDataObject = {};
  formData.forEach((value, key) => {
      formDataObject[key] = value;
  });
  //console.log('Datos del FormData:', formDataObject);

  // Enviar los datos usando fetch
  fetch('../data/update_tarifas.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
      },
      body: JSON.stringify(formDataObject)
  })
  .then(response => response.json())
  .then(data => {
      // Manejar la respuesta de PHP
      if (data.success) {
        alertContainerTarifas.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
      } else {
        alertContainerTarifas.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
      }

      // Ocultar la alerta después de unos segundos
      setTimeout(() => {
        alertContainerTarifas.innerHTML = '';
      }, 10000);
  })
  .catch(error => {
      console.error('Error:', error);
      alert('Hubo un problema con el envío del formulario.');
  });
});

/* FIN SISTEMA */
