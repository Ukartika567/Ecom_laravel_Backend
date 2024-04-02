<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Laravel Send Email to Multiple Users - Nicesnippets.com</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-9 p-0">
                        <h5><b>emailss - <span class="text-danger">Divyaltech.com</span></b></h5>
                    </div>
                    <div class="col-md-3 text-right mb-2 p-0">
                        <button type="button" class="btn btn-primary send-mail btn-sm" disabled="disabled"> <i class="fa fa-share"></i> Send Mail</button>
                    </div>
                    <div class="col-md-12 success-mail p-0" style="display: none;">
                        <div class="alert alert-success">
                          Sent Mail Successfully.
                        </div>
                    </div>
                </div>
                <div class="row">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox" value="1" name="user-all" class="user-all"></th>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($users->count() > 0)
                                @foreach($users as $key => $value)
                                    <tr>
                                        <td>
                                            {{ Form::checkbox('ckeck_user', 1, false,['class'=>'ckeck_user','data-id' => $value->id ]) }}
                                        </td>
                                        <td>{{ $value->name }}</td>
                                        <td>{{ $value->email }}</td>
                                        
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    $('.user-all').change(function (e) {
        var value = $('.user-all:checked').val();
        if (value == 1) {
            $('input[name="ckeck_user"]').prop('checked',true);
            $('.send-mail').removeAttr('disabled');
        }else{
            $('input[name="ckeck_user"]').prop('checked',false);
            $('.send-mail').attr('disabled','disabled');
        }
    });  

    $("input[name='ckeck_user']").change(function () {
        if ($("input[name='ckeck_user']:checked").length > 0) {
          $('.send-mail').removeAttr('disabled');
        }else{
         $('.send-mail').attr('disabled','disabled');
       }
     });

    $('.send-mail').click(function (e) {
        e.preventDefault();
        var ids = [];

        $.each($('input[name="ckeck_user"]:checked'),function(){
            ids.push($(this).data('id'));
        });

        if (ids != '') {
            $(this).attr("disabled", true);
            $(this).html('<i class="fa fa-spinner fa-spin"></i> Send Mail');
            $.ajax({
                url: '{{ route('send.mail') }}',
                type: 'POST',
                data: {
                    _token:$('meta[name="csrf-token"]').attr('content'), 
                    ids:ids
                },
                success: function (data) {
                    $('.success-mail').css('display','block');
                    $('.send-mail').attr("disabled", false);
                    $('.send-mail').html('<i class="fa fa-share"></i> Send Mail');
                }
            });
        }
    });
</script>
</html>