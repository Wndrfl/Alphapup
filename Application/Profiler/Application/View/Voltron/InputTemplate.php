<input 
	type="<?php echo isset($type) ? $type : 'text'; ?>" 
	value="<?php echo isset($value) ? $value : ''; ?>" 
	<?php $this->view('Alphapup','Application/View/Voltron/AttributesTemplate.php',array(),array(
		'attr' => (isset($attr)) ? $attr : false,
		'disabled' => (isset($disabled)) ? $disabled : false,
		'id' => (isset($id)) ? $id : false,
		'name' => (isset($name)) ? $name : false,
		'required' => (isset($required)) ? $required : false
	)); ?>
	<?php if(isset($autocomplete)) { ?> autocomplete="<?php echo ((bool)$autocomplete == true) ? 'on' : 'off'; ?>"<?php } ?>
	<?php if(isset($autofocus) && $autofocus == true) { ?> autofocus="autofocus"<?php } ?>
	<?php if(isset($max) && $max != null) { ?> max="<?php echo $this->escape($max); ?>"<?php } ?>
	<?php if(isset($maxlength) && $maxlength != null) { ?> maxlength="<?php echo $this->escape($maxlength); ?>"<?php } ?>
	<?php if(isset($min) && $min != null) { ?> min="<?php echo $this->escape($min); ?>"<?php } ?>
	<?php if(isset($multiple) && $multiple != null) { ?> multiple="multiple"<?php } ?>
	<?php if(isset($pattern) && $pattern != null) { ?> pattern="<?php echo $this->escape($pattern); ?>"<?php } ?>
	<?php if(isset($placeholder) && $placeholder != null) { ?> placeholder="<?php echo $this->escape($placeholder); ?>"<?php } ?>
	<?php if(isset($step) && $step != null) { ?> step="<?php echo $this->escape($step); ?>"<?php } ?>
/>