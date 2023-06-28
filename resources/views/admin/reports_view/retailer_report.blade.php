<style type="text/css">
table, th, td {
  border: 1px solid black;
}
</style>
<h2>Retailer Report</h2>
<table>
 
  <tr>
    <th>ID</th>
    <th>Retailer Name</th>
    <th>Email</th>
    <th>Contact No.</th>
    <th>Retailer Company Name</th>
    <!-- <th>Company Description</th>
    <th>Company Website</th> -->
    <th>Approval Status</th>
    <th>Net 30 Status</th>
    <th>Billing Address</th>
    <th>Shipping Address</th>
    <th>Status</th>
  </tr>

@foreach($data as $key => $value)
  
  <tr>
    <td>{{$value['#']}}</td>
    <td>{{$value['Retailer Name']}}</td>
    <td>{{$value['Email']}}</td>
    <td>{{$value['Contact No']}}</td>
    <td>{{$value['Retailer Company Name']}}</td>
    <!-- <td>{{$value['Company Description']}}</td>
    <td>{{$value['Company Website']}}</td> -->
    <td>{{$value['Approval Status']}}</td>
    <td>{{$value['Net 30 Status']}}</td>
    <td>{{$value['Billing Address']}}</td>
    <td>{{$value['Shipping Address']}}</td>
    <td>{{$value['Status']}}</td>

  </tr>
  
  
@endforeach

  
</table>
