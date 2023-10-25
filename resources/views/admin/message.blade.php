<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

@if(Session::has('success'))
    <div id="success-alert" class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i> Success!</h4> {{ Session::get('success') }}
    </div>

    <script>
        setTimeout(function(){
            $('#success-alert').fadeOut('slow');
        }, 3000);
    </script>
@endif

@if(Session::has('error'))
    <div id="error-alert" class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-ban"></i> Error!</h4>  {{ Session::get('error') }}
    </div>

    <script>
        setTimeout(function(){
            $('#error-alert').fadeOut('slow');
        }, 3000 );
    </script>
@endif