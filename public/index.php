<?php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test site</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
      var processingStockAmountsRequest = false;

      var doJob = function(){
        var dtext = '';

        /*Warehouse*/
        var wid = $("#invoice_warehouse_id").val();
        dtext += wid + ' ';

        /*Items*/
        var ids = [];
        var items = [];
        $("tr[data-reference_id]").each(function(index,el){
          var p = $(el).find(".productID").val();
          var a = $(el).find(".amount").val();
          ids.push(p);
          items.push({prid:p, amount:a});
        });

        // Get data from api
        $.ajax({
          url: 'http://localhost:4570/api.php',
          data: { request:'getStockAmounts', warehouseID: wid, productIDs:ids.toString() },
          success: function(data, status, xhr){
            // Handle data
            var inStock = true;
            var r = data.records;
            for(var i=0; i<items.length; ++i){
              var found = null;
              for(var p=0; p<r.length; ++p){
                console.log(JSON.stringify(r[p]));
                if(parseInt(r[p].productID) === parseInt(items[i].prid)){
                  found = r[p];
                  break;
                }
              }
              if(!found){
                found = {productID:i.prid, amountInStock:0};
              }
              if(parseFloat(items[i].amount) > 0
                && parseFloat(items[i].amount) > parseFloat(found.amountInStock)){
                inStock = false;
                break;
              }
            }
            if(inStock){
              alert("In stock");
            } else {
              alert("Not in stock");
            }
          },
          error: function(data, status, xhr){
            if(data.responseJSON.message){
              console.log("Request failed.\n" + JSON.stringify(data.responseJSON.message));
            } else {
              console.log("Request failed.\n" + JSON.stringify(data));
            }
          },
          complete: function(){
            processingStockAmountsRequest = false;
          },
          dataType: 'json'
        });

        // Update message
        dtext += ids.toString() + '<br>' + JSON.stringify(items);
        $("#demo").html(dtext);
      };

      var btnClicked = function(){
        if(!processingStockAmountsRequest){
          processingStockAmountsRequest = true;
          doJob();
        }
      };
    </script>
</head>
<body>
<h1>Let's test it!</h1>
<button id="btn1" onclick="btnClicked()">Push me</button>
<p id="demo"></p>

<form name="form" id="form">
    <label class="form-select form-select-lg"> Ladu:
        <select name="invoice_warehouse_id" id="invoice_warehouse_id">
            <option value="1" selected="">Põhiladu</option>
            <option value="2"></option>
        </select>
    </label>
    <table id="invoiceGrid">
        <tbody>
        <tr id="row1" data-reference_id="1">
            <td><input type="text" class="productCode" name="code1" id="code1" value="69-1186"></td>
            <td>
                <div>
                    <input type="hidden" class="rowID" name="id1" id="id1" value="">
                    <input type="text" class="productID" name="product_id1" id="product_id1" value="513">
                </div>
            </td>
            <td><input type="tel" class="form-control amount text-right" name="amount1" id="amount1"></td>
        </tr>
        <tr id="row2" data-reference_id="2">
            <td><input type="text" class="productCode" name="code2" id="code2" value="69-1187"></td>
            <td>
                <div>
                    <input type="hidden" class="rowID" name="id2" id="id2" value="">
                    <input type="text" class="productID" name="product_id2" id="product_id2" value="514">
                </div>
            </td>
            <td><input type="tel" class="form-control amount text-right" name="amount2" id="amount2"></td>
        </tr>
        <tr id="row3" data-reference_id="3">
            <td><input type="text" class="productCode" name="code3" id="code3" value="69-1188"></td>
            <td>
                <div>
                    <input type="hidden" class="rowID" name="id3" id="id3" value="">
                    <input type="text" class="productID" name="product_id3" id="product_id3" value="515">
                </div>
            </td>
            <td><input type="tel" class="form-control amount text-right" name="amount3" id="amount3"></td>
        </tr>
        <tr id="row4">
        </tr>
        <tr id="row5">
        </tr>
        </tbody>
    </table>
</form>

</body>
</html>
