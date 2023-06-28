<!-- HEader -->        
@include('maker.layout._header')    
        
<!-- BEGIN Sidebar -->
@include('maker.layout._sidebar')
<!-- END Sidebar -->

<!-- BEGIN Content -->
<div id="main-content">
    @yield('main_content')
</div>
    <!-- END Main Content -->

<!-- Footer -->        
@include('maker.layout._footer')    
                
              