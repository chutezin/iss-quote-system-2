jQuery(document).ready(function ($) {

    console.log('active_pdf_scripts');
    
    var button = $('.print_pdf'),
        id = button.data('order-number-rqaq'),
        liresponse = $('li.response');
         
    button.click(function (e) { 
        e.preventDefault();
        let formDataValue = {
            action: "ajax_pdf_generator",  
            order_number_rqa: id,    
       
        }
    
       $.ajax({
           type: "post",
           url: ajaxPdf.ajaxurl,
           data: formDataValue,             

           beforeSend: function (response,data) {
            console.log('before sending hook:');
            console.log('response:', response);
            console.log('data:', data);
            button.after('');
            },           
           success: function (response,data) {
               console.log(response)
               console.log(data)
            if( data.success){
                console.log('success:', response);
                console.log(data);
                liresponse.html(response);
            } else{
                console.log('error:', response);
                console.log('data:',data);
                liresponse.html(response);
            }
           },           
       });   
    
        
    });
      


    
    
    
    });