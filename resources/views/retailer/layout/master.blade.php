<!-- HEader -->        
@include('retailer.layout._header')    
        
<!-- BEGIN Sidebar -->
@include('retailer.layout._sidebar')
<!-- END Sidebar -->

<!-- BEGIN Content -->
<div id="main-content">
    @yield('main_content')
</div>
    <!-- END Main Content -->

<!-- Footer -->        
@include('retailer.layout._footer')    
                
              