jQuery(document).ready(function ($) {
    var formTrigger = $('.rqaq_form_trigger'),
        formData = $('.rqaq_form_data'),
        buttonLogin = $('#loginUP'),
        modal = $('#popUpModalBackground'),
        loginModalContent = $('#modalLoginRegister'),
        register = $('#registerModal'),
        login = $('#loginModal'),
        loginTab = $('#rqaq_form_login_data'),
        loginTrigger =  $('#rqaq_form_login_trigger'),
        contactInfoTab = $('#rqaq_form_ShippingAdress_data'),
        contactInfoTrigger = $('#rqaq_form_ShippingAdress_trigger'),
        noteTab = $('#rqaq_form_actionsButtons_data'),
        noteTrigger = $('#rqaq_form_actionsButtons_trigger'),
        continueForm = $('a.continueForm'),
        continueNote = $('a.personalNote'),
        body = $('body');
    body.addClass('rqaq-form-page');  
    formTrigger.click(function (e) { 
        e.preventDefault();
        var data = $(this).next( formData );  
        var span =$(this).find('span')
       data.toggle();
       if( data.is(':visible')){
          span.html('-');
       } else{
           span.html('+');
       }
    });
    continueForm.click( function(e){
        e.preventDefault();
        loginTab.hide();
        contactInfoTab.show();
        span = loginTrigger.find('span');
         span.html('+');
        span2 = contactInfoTrigger.find('span');
        span2.html('-');
        $('html, body').animate({
            scrollTop: contactInfoTab.offset().top
        }, 1000);
        noteTab.show();
    });
    continueNote.click( function(e){
        e.preventDefault();
        $('html, body').animate({
            scrollTop: noteTab.offset().top
        }, 1000);
        noteTab.show();
        span = contactInfoTrigger.find('span');
         span.html('+');
    });
    if (buttonLogin != null) {
        buttonLogin.click(function (e) {
            e.preventDefault();
            modal.show();
            loginModalContent.show();
            register.hide();
            login.show();
        })
    };
    var form = $('#iss_rqaq_request_form'),  
        country = form.find('[name|="rqa_country"]'),
        renderState = form.find('#renderState'),
        option = $('<option></option>').attr("value", "option value").text("Loading"),
        selectGen = $('<select id="rqa_state"></select>').attr({name:"rqa_state", required: true}),
        selectID =  $("#rqa_state"),
        inputGen = $('<input>').attr({name: "rqa_state", type: "text", id:"rqa_state", required: true});
   country.change(function (e) { 
        e.preventDefault();
        let fillStates = {
            action: "fill_state",           
            rqa_country: country.val(),
        }
      $.ajax({
          type: "post",
          url: ajaxForm.ajaxurl,
          data: fillStates,
          dataType: "json",
          beforeSend: function(response,data){
              country.prop("disabled", true);
             renderState.empty();
             renderState.append(selectGen);         
            selectGen.empty().append(option);
          },
          success: function (data) {
            country.prop("disabled", false);
              data = data.data;
             selectGen.empty();   
            if( data['error'] || $.isEmptyObject(data) ){
                renderState.empty();
                renderState.append(inputGen);    
            }else{
                $.each(data, function (index, element) {
                    selectGen.append( $("<option></option>")
                     .attr("value", index).html(element));
                 });
            }
          }
      });
    });
// open form
jQuery.fn.clickToggle = function(a, b) {
    return this.on("click", function(ev) { [b, a][this.$_io ^= 1].call(this, ev) })
  };
  // TEST:
  $('#showCartDetails').clickToggle(function(ev) {
    $(this).text("Hide cart details"); 
    $('table#rqaq_cart_contents').slideDown('fast');
  }, function(ev) {
    $(this).text("Show cart details");
    $('table#rqaq_cart_contents').slideUp('fast');
  });
});