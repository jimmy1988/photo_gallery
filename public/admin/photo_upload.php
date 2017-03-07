<?php
	require_once("../../includes/initialize.php");

	if(!$session->is_logged_in()){ redirect_to("login.php"); }

?>

<?php
	$max_file_size=1048576;
	//expressed in byted
	//102040=10KB
	//102400=100KB
	//1048576=1MB
	//10485760=10MB

	$message="";
	if(isset($_POST['submit'])){
		$photo = new Photograph();
		$photo->caption = $_POST['caption'];
		$photo->attach_file($_FILES['file_upload']);
		if($photo->save()){
			//Success
			$session->message("Photograph uploaded successfully");
			redirect_to("list_photos.php");
		}else{
			//Failure
			$message=join("<br/>", $photo->errors);
		}
	}
?>

<?php include_layout_template('admin_header.php')?>

	<h2>Photo Upload</h2>

	<?php echo output_message($message); ?>

	<form action="photo_upload.php" enctype="multipart/form-data" method="post">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php if(isset($max_file_size)){ echo $max_file_size ;} else{ echo "1048576"; } ?>"/>
		<p><input type="file" name="file_upload" /></p>
		<p>Caption: <input type="text" name="caption" value=""/></p>
		<input type="submit" name="submit" value="Upload" />
	</form>

<?php include_layout_template('admin_footer.php')?>
