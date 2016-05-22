<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Regist</title>
	<link rel="stylesheet" href="<?php echo base_url() ?>public/css/bootstrap.min.css">
</head>
<body>
	<div id="content" class="col-md-offset-3 col-md-6">
		<h1>Upload avata</h1>
		<div id="avata" class="row" style="text-align: center">
			<img style="margin: 20px auto" src="<?php echo base_url() ?>/upload/<?php echo $info->avata ?>" alt="" width="50%">
		</div>
		<form action="" method="post" accept-charset="utf-8" enctype="multipart/form-data" class="form-horizontal">
			<div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">Avata</label>
                <div class="col-sm-10">
                  	<input type="file" name="avata" value="" placeholder="" class="form-control">
                  	<div id="user-error" class="clear error bg-danger" name="name_error"><?php echo form_error('user') ?></div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10" style="text-align: right">
                  	<input type="submit" name="submit" class="btn btn-success" value="Post">
                </div>
            </div>
		</form>
</div>
</body>
</html>