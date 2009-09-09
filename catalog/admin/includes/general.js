function SetFocus() {
  if (document.forms.length > 0) {
    isNotAdminLanguage:
    for (f=0; f<document.forms.length; f++) {
      if (document.forms[f].name != "adminlanguage") {
        var field = document.forms[f];
        for (i=0; i<field.length; i++) {
          if ( (field.elements[i].type != "image") &&
               (field.elements[i].type != "hidden") &&
               (field.elements[i].type != "reset") &&
               (field.elements[i].type != "submit") ) {

            document.forms[f].elements[i].focus();

            if ( (field.elements[i].type == "text") ||
                 (field.elements[i].type == "password") )
              document.forms[f].elements[i].select();

            break isNotAdminLanguage;
          }
        }
      }
    }
  }
}

function rowOverEffect(object) {
  if (object.className == 'dataTableRow') object.className = 'dataTableRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'dataTableRowOver') object.className = 'dataTableRow';
}

function toggleDivBlock(id) {
  if (document.getElementById) {
    itm = document.getElementById(id);
  } else if (document.all){
    itm = document.all[id];
  } else if (document.layers){
    itm = document.layers[id];
  }

  if (itm) {
    if (itm.style.display != "none") {
      itm.style.display = "none";
    } else {
      itm.style.display = "block";
    }
  }
}

function stopEnter(event) {
  var evt;
  var charCode;
  evt = event ? event : window.event;
  charCode = evt.which ? evt.which : evt.keyCode;
  if (13 == charCode) {
    //alert(charCode);
    if(evt.which){
      evt.preventDefault();
    } else {
      evt.keyCode = 9;
    }
    document.getElementById("password").focus();
  }
}	

function performPrePostChecks() {
  if(document.getElementById("username").value == "") {
    document.getElementById("username").focus();
    return false;
  } else if(document.getElementById("password").value == "") {
    document.getElementById("password").focus();
    return false;
  }
  return true;
}

function check_password() {
  var error = false;
  var error_message = "Errors have occured during the process of your form!\nPlease make the following corrections:\n\n";
  
  var password = document.getElementById("password").value;
  var confirmation = document.getElementById("conform_password").value;
  var fname = document.getElementById("admin_fname").value;
  var lname = document.getElementById("admin_lname").value;
  var otp = document.getElementById("username").value;

  if (fname.length < 2) {
    error_message = error_message + "* First Name must contain a minimum of 2 characters.\n";
    error = true;
  }

  if (lname.length < 2) {
    error_message = error_message + "* Last Name must contain a minimum of 2 characters.\n";
    error = true;
  }

  if (otp.length < 12) {
    error_message = error_message + "* Please provide a valid YubiKey OTP.\n";
    error = true;
  }

  if (password.length < 5) {
    error_message = error_message + "* Password must contain a minimum of 5 characters.\n";
    error = true;
  } else if (password != confirmation) {
    error_message = error_message + "* The Password Confirmation must match your Password.\n";
    error = true;
  }

  if (error) {
    alert(error_message);
    return false;
  }
  return true;
}
