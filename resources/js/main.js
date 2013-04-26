/* Author:

*/

function replaceAll(str,regex,repl) {
  return str.replace(new RegExp(regex,"g"),repl);
}

function ajax_load_entry(id) {
  $('#la_' + replaceAll(id,":","_")).load('miniview.php?uri='+id,function(response, status, xhr) {
    if (status == "error" && $('#la_' + id).text().indexOf('More information unavailable') < 0) {
      $('#la_' + replaceAll(id,":","_")).append('More information unavailable');
      $('#la_' + replaceAll(id,":","_")).slideDown();
    } else {
      $('#la_' + replaceAll(id,":","_")).slideDown("slow");
    }    
  });
}


function ajax_load_entry_bl(id) {
  $('#labl_' + replaceAll(id,":","_")).load('miniview.php?uri='+id,function(response, status, xhr) {
    if (status == "error" && $('#labl_' + id).text().indexOf('More information unavailable') < 0) {
      $('#labl_' + replaceAll(id,":","_")).append('More information unavailable');
      $('#labl_' + replaceAll(id,":","_")).slideDown();
    } else {
      $('#labl_' + replaceAll(id,":","_")).slideDown("slow");
    }    
  });
}
