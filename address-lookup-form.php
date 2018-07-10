<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
<?php 

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"https://oauth.nzpost.co.nz/as/token.oauth2");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,
    "grant_type=client_credentials&client_id=YOUR_CLIENT_ID&client_secret=YOUR_CLIENT_SECRET");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec ($ch);

curl_close ($ch);
$json = json_decode($server_output, true);
$access_token = $json["access_token"];

#Curl Request for Parsel Address token API with Different Client ID and Secret
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL,"https://oauth.nzpost.co.nz/as/token.oauth2");
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS,
    "grant_type=client_credentials&client_id=YOUR_CLIENT_ID&client_secret=YOUR_CLIENT_SECRET");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec ($curl);

curl_close ($curl);
$json = json_decode($output, true);
$parsel_addr_access_token = $json["access_token"];

?>

<input type="hidden" id="access_token" name="access_token" value="<?php echo $access_token; ?>">
<input type="hidden" id="parsel_addr_access_token" name="parsel_addr_access_token" value="<?php echo $parsel_addr_access_token; ?>">
<div id="sf3" class="frm" style="display: none;">
    <fieldset>
        <legend>Address</legend>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="col-lg-4 control-label" for="primary_address_street">Street
                Address: </label>
                <div class="col-lg-6">
                    <input type="text" placeholder="Street Address" id="primary_address_street"
                    name="primary_address_street" class="form-control" autocomplete="off" pattern=".{3,10}" required="3 characters minimum">
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="col-lg-4 control-label" for="primary_address_state">Address Line
                2: </label>
                <div class="col-lg-6">
                    <input type="text" placeholder="Address Line 2" id="primary_address_state"
                    name="primary_address_state" class="form-control" autocomplete="off" required>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="col-lg-4 control-label" for="primary_address_city">City: </label>
                <div class="col-lg-6">
                    <input type="text" placeholder="City" id="primary_address_city"
                    name="primary_address_city" class="form-control" autocomplete="off">
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="col-lg-4 control-label" for="primary_address_postalcode">Postal
                Code: </label>
                <div class="col-lg-6">
                    <input type="text" placeholder="Postal Code" id="primary_address_postalcode"
                    name="primary_address_postalcode" class="form-control"
                    autocomplete="off">
                </div>
            </div>
        </div>
   </fieldset>
</div>

<script src="https://code.jquery.com/ui/1.10.2/jquery-ui.js" ></script>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>


<script type="text/javascript">


var result_array = [];
$("#primary_address_street").autocomplete({
    source: function (request, response) {
        var access_token = document.getElementById("access_token").value;
        $.ajax({
          url: "https://api.nzpost.co.nz/addresschecker/1.0/find",
          beforeSend: function(xhr) {
             xhr.setRequestHeader("Authorization", "Bearer "+ access_token)
             xhr.setRequestHeader( "Accept", "application/json")
         },
         data: {
            address_line_1: request.term,
            max: "20",
            type: "Postal" },
            success: function (data) {
                result = data["addresses"].map(function(a) {return [a.FullAddress, a.DPID];});
                var coll = result.map(function(value,index) { return value[0]; })
                response(coll);
                result_array.push(result);
            }
        })
    },
    select: function( event , ui ) {
        var selectectedText = ui.item.label;
        console.log( "You selected: " + selectectedText );
        document.getElementById('primary_address_street').value = "";
        var dpid_array;
        for( var i = 0, len = result.length; i < len; i++ ) {
            if( result[i][0] ===  selectectedText) {
                dpid_array = result[i];
                break;
            }
        }
        dpid = dpid_array[1];
        var parsel_addr_access_token = document.getElementById("parsel_addr_access_token").value;
        $.ajax({
          url: "https://api.nzpost.co.nz/parceladdress/2.0/domestic/addresses/dpid/"+dpid,
          beforeSend: function(xhr) {
             xhr.setRequestHeader("Authorization", "Bearer "+ parsel_addr_access_token)
             xhr.setRequestHeader( "Accept", "application/json")
         },
         success: function (data) {
            console.log(data);
            document.getElementById('primary_address_street').value = data.address.street_number + " " +data.address.street+ " " +data.address.street_type;
            document.getElementById('primary_address_state').value =  data.address.suburb;
            document.getElementById('primary_address_city').value = data.address.city;
            document.getElementById('primary_address_postalcode').value = data.address.postcode;
        }
    })
    }
});

</script>
