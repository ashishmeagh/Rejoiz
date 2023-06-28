<style type="text/css">
table, th, td {
  border: 1px solid black;
}
</style>

<h2>Vendor Report</h2>

<table>
 
  <tr>
    <th>ID</th>
    <th>Tax Id</th>
    <th>User Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Brand Name</th>
    <th>Company Name</th>
    <th>Status</th>
  </tr>

@foreach($data as $key => $value)
  
  <tr>
    <td>{{$value['user_id']}}</td>
    <td>{{$value['tax_id']}}</td>
    <td>{{$value['user_name']}}</td>
    <td>{{$value['email']}}</td>
    <td>{{$value['phone']}}</td>
    <td>{{$value['brand_name']}}</td>
    <td>{{$value['company_name']}}</td>
    <td>{{$value['status']}}</td>
  </tr>
  
@endforeach

  
</table>
