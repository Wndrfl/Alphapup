<input
	type="radio"
	value="<?php echo $value; ?>"
	<?php $this->view('Alphapup','Application/View/Voltron/AttributesTemplate.php',array(),array(
		'attr' => (isset($attr)) ? $attr : false,
		'disabled' => (isset($disabled)) ? $disabled : false,
		'id' => (isset($id)) ? $id : false,
		'name' => (isset($name)) ? $name : false,
		'required' => (isset($required)) ? $required : false
	)); ?>
	<?php if($checked) { ?> checked="checked"<?php } ?>
/><?php if(isset($label)) { echo ' '.$label;} ?>