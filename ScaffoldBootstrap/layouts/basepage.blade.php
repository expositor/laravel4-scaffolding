<!DOCTYPE html>
<html lang="en">
  <head>
    @include('layouts._head')
  </head>
  
  <body>
    
    @include('layouts._navbar')

    <div class="container">

      <div class="starter-template">
        @yield('body')
      </div>

    </div><!-- /.container -->

    
    @include('layouts._footer')
  </body>
</html>