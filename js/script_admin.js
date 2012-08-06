
jQuery(document).ready(function($){
    

    
    
        $('#tpSave').bind('click',function(evt){
        evt.preventDefault();
        var mails =$('#mails').val();
        var cron =$('#cronSelect').val();
       
        

        
            $.ajax({
            type :  "post",
            url : ajaxurl,
            timeout : 5000,
            data : {
                'action' : 'posts_stat',
                'mails' : mails,		  
                'cron' : cron		  
            },			
            success :  function(data){  
                alert('Saved Successfully');
               
            }
        })	//end of ajax	
        
        })
})
