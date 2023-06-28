<!-- HEader -->        
@include('representative.layout._header')    
        
<!-- BEGIN Sidebar -->
@include('representative.layout._sidebar')
<!-- END Sidebar -->

<!-- BEGIN Content -->
<div id="main-content">
    @yield('main_content')
</div>
    <!-- END Main Content -->

<!-- Footer -->        
@include('representative.layout._footer')    
                
              