jQuery(document).ready(function () {


var h = new Date().getHours()         // Get the hour (0-23)
var m = new Date().getMinutes()       // Get the minutes (0-59)
var s = new Date().getSeconds() 



  console.log(h+'H'+m+':'+s);

  var table99 = jQuery('#dataTableVisitors').DataTable({
    "processing": true,
    "serverSide": true,
    "order": [[0, "desc"]],
    "columnDefs": [
      {
        "targets": 0, // -1
        "data": null,
        "defaultContent": "<button>Whitelist</button>"
      },
      {
        "targets": 2,
        "createdCell": function (td, cellData, rowData, row, col) {
          if (cellData == 'OK') {
            jQuery(td).css("background-color", "#A9DFBF");
          }
          if (cellData == 'Denied') {
            jQuery(td).css("background-color", "#F5B7B1 ");
          }
        },
      }],
    "ajax": {
      "url": "/wp-content/plugins/antibots/table/server_processing.php",
      "data": {
        key: bill_key._key
      },
      error: function (jqXHR, textStatus, errorThrown) {
        alert("Unexpected error. Please, try again later.");
      }
    },
    dataType: "json",
    contentType: "application/json",
  });


  jQuery("#dataTableVisitors tbody").on('click', 'tr', function () {
    var $row = table99.row(jQuery(this).closest('tr')); // .data();
    var rowIdx = table99.row(jQuery(this).closest('tr')).index();
    $ip = $row.cell(rowIdx, 3).data();
    // alert($ip);



   // var name = $(this).data('name');
    // console.log(name);

    // alert(name);

   // jQuery('#addIP').modal({ show: true });

   // jQuery(".modal-body").html($ip);

   /*
   if (  jQuery( "#dialog-confirm" ).is(":visible"))    
   {
    // alert('1');
   }
   else
   {
*/

      jQuery( "#dialog-confirm" ).dialog({
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
          "Add to Whitelist": function() {

              // console.log($ip);

              jQuery.ajax({
                url: ajaxurl,
                /*   type: "POST", */
                data: {
                    'action': 'antibots_add_whitelist',
                    'ip': $ip
                },
                success: function (data) {
                },
                error: function (errorThrown) {
                    // console.log(errorThrown);
                }
            });            

            jQuery( this ).dialog( "close" );


          },
          Cancel: function() {
            jQuery( this ).dialog( "close" );
          }
        }

      });

      jQuery("#modal-body").html('Add IP: '+$ip+' to whitelist?');
 
 // }


  });
});
/*
error: function(jqXHR, textStatus, errorThrown)
{
    // Note: You can use "textStatus" to describe the error.
    // Custom
    switch(jqXHR.status)
    {
        case 404:
            alert('Requested page not found. [404]');
        break;
        case 500:
            alert('Internal Server Error [500]');
        break;
        default:
            alert('Unexpected unknow error');
        break:
    }
*/