<textarea 
	value="" 
	<?php $this->view('Alphapup','Application/View/Voltron/AttributesTemplate.php',array(),array(
		'attr' => (isset($attr)) ? $attr : false,
		'disabled' => (isset($disabled)) ? $disabled : false,
		'id' => (isset($id)) ? $id : false,
		'name' => (isset($name)) ? $name : false,
		'required' => (isset($required)) ? $required : false
	)); ?>
	<?php if(isset($autofocus) && $autofocus == true) { ?> autofocus="autofocus"<?php } ?>
	<?php if(isset($cols) && $cols != null) { ?> rows="<?php echo $this->escape($cols); ?>"<?php } ?>
	<?php if(isset($maxlength) && $maxlength != null) { ?> maxlength="<?php echo $this->escape($maxlength); ?>"<?php } ?>
	<?php if(isset($placeholder) && $placeholder != null) { ?> placeholder="<?php echo $this->escape($placeholder); ?>"<?php } ?>
	<?php if(isset($required) && $required != null) { ?> required="<?php echo $this->escape($required); ?>"<?php } ?>
	<?php if(isset($rows) && $rows != null) { ?> rows="<?php echo $this->escape($rows); ?>"<?php } ?>
	<?php if(isset($wrap) && $wrap != null) { ?> rows="<?php echo $this->escape($wrap); ?>"<?php } ?>
><?php echo isset($value) ? $value : ''; ?></textarea>