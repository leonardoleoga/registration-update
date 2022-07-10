let updateData = {
  'validateCPF': function() {
    let cpf = $('#input-custom-field3').val().replace(/[^\d]+/g,'')

    if (cpf.length > 1 && cpf.length <= 10) {
      errorCPF(cpf)
    } else if (cpf == "00000000000" || cpf == "11111111111" || cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999" || cpf == "12345678909") {
      errorCPF(cpf)
    } else if (cpf.length == 11) {
      let resto
      let soma = 0

      for (i = 1; i <= 9; i++)
        soma = soma + parseInt(cpf.substring(i-1, i)) * (11 - i)

      resto = (soma * 10) % 11

      if ((resto == 10) || (resto == 11))
        resto = 0

      if (resto != parseInt(cpf.substring(9, 10))) {
        errorCPF(cpf)
      } else {
        soma = 0

        for (i = 1; i <= 10; i++)
          soma = soma + parseInt(cpf.substring(i-1, i)) * (12 - i)

        resto = (soma * 10) % 11

        if ((resto == 10) || (resto == 11))
          resto = 0

        if (resto != parseInt(cpf.substring(10, 11)))
          errorCPF(cpf)
      }
    }
  },
  'sethtml': function(json) {

  },
  'initGetData': function() {
		$.ajax({
			url: 'index.php?route=account/renovate/getCustomerData',
			dataType: 'json',
			cache: true,
			success: function(json) {
				updateData.sethtml(json);
			}
		});
	}
}

$(window).load(function() {
	updateData.initGetData();
});