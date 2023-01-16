

//o user ta no celular?
function is_mobile(){
  if(navigator.userAgent.match(/(iPhone)|(iPod)|(Android)|(webOS)|(iPad)|(BlackBerry)|(Windows Phone)|(Opera Mini)|(IEMobile)|(Nokia)|(MeeGo)/i)){
    return true;
  }else{
    return false;
  }

}


//mostra um pop-up de alerta
function alert_most(value,var_out_num) {
  var element = document.getElementById('alert_save');
  var element2 = document.getElementById('hidden_user_err');
  if(value) {
      element.classList.add("show");
  } else {
      element.classList.remove("show");
      element2.classList.remove("show");
      $(".form-group").removeClass("has-error");
      $(".help-block").remove();

        event.preventDefault();
        var data = send_saidas(var_out_num);

        if (!data) {
            element2.classList.add("show");
        }
  }
}


//envia os valores inseridos no formulario pelo user
function send_saidas(var_out_num) {
  //salvar configuracoes

  var semana = [];
  semana[0]  = ($("#ck7:checked").val() === undefined) ? null : 0;//domingo
  semana[1]  = ($("#ck1:checked").val() === undefined) ? null : 1;
  semana[2]  = ($("#ck2:checked").val() === undefined) ? null : 2;
  semana[3]  = ($("#ck3:checked").val() === undefined) ? null : 3;
  semana[4]  = ($("#ck4:checked").val() === undefined) ? null : 4;
  semana[5]  = ($("#ck5:checked").val() === undefined) ? null : 5;
  semana[6]  = ($("#ck6:checked").val() === undefined) ? null : 6;//sabado

  var dias_irrigar = '';
  var flag_first_caracter = 0;
  for (let i = 0; i < semana.length; i++) {
    if (semana[i] !== null) {
      if (flag_first_caracter !== 0) {//se nao for o primeiro caracter adiciona virgula
        dias_irrigar += ':';
      }
      dias_irrigar += semana[i];
      flag_first_caracter++;
    } 
  }
  if(dias_irrigar == ''){
    //console.error('array vazio');
    dias_irrigar = '7';// no server identificar que se for 7 é pra nao irrigar nenhum dia entao
  }


  var formData = {
      out_num: var_out_num,
      id_plant: parseInt($("#plant").val()),
      id_control: parseInt($("#control").val()),
      hr_inicio: $("#inicio").val(),
      hr_fim: $("#final").val(),
      dias_semana: dias_irrigar
  };
  

  console.log(formData);

  
  $.ajax({
      method:"POST",
      dataType:"json",
      url:"/api/back/set.saida.php",
      data:formData
  }).done(function (data) {
      console.log(data);
      if (data.success == false) {
          if (data.errors != null) { //se tiver erros, pois pode so ter respondido com sucesso == false mas sem erros
            alert(data.errors);
            return false;
          }else{
            alert('Não foi possivel configurar esta saida, verifique sua conexão com a internet e tente novamente.')
          }
      } else {
        $("form").html(
          '<div class="alert alert-success"><h1>Sucesso</h1> <br>'+data.server_response+' <br>Recarrregue a página pra editar novos valores</div>'
        );
      
      }
  }).fail(function (data) {
        console.log(data);
        $("form").html(
          '<div class="alert alert-danger">Oooops, ouve um erro, verifique sua conexão com a internet ou entre em contato <a href="./suporte.php">clicando aqui.</a></div>'
        );
      });
      return true;

}

//exibe uma parte do texto, TODO: refatorar para ficar mais dinamico
function ler_mais(identify) {
  var dots = document.getElementById("dots");
  var moreText = document.getElementById(identify+"_text");
  var btnText = document.getElementById(identify+"_btn");

  if (dots.style.display === "none") {
    dots.style.display = "inline";
    btnText.innerHTML = "Ler Mais";
    moreText.style.display = "none";
  } else {
    dots.style.display = "none";
    btnText.innerHTML = "Ler Menos";
    moreText.style.display = "inline";
  }
}

//usado para marcar a os inputs do tipo selects em formularios
function define_select(id,value){
  document.getElementById(id).options[value].setAttribute('selected','selected');
}


//exibe a categoria temporizador caso ela seja selecionada
function mostrar_tipo_controle() {
  var tipo_controle = document.getElementById('control').value;

  if (tipo_controle == 2){//2 -> modo temporizador
    if (window.jQuery) {
      $("#temporizador").fadeIn(100)
    } else {
        setTimeout(function() {mostrar_tipo_controle();}, 50);//aguarda 50ms e executa a funcao dnv ate o jquery ter carregado
    }
  } else {
    $("#temporizador").fadeOut();
  }

}