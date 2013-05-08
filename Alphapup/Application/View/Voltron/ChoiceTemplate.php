<?php if(!$expanded) { ?>
	<select
		<?php $this->view('Alphapup','Application/View/Voltron/AttributesTemplate.php',array(),array(
			'attr' => (isset($attr)) ? $attr : false,
			'disabled' => (isset($disabled)) ? $disabled : false,
			'id' => (isset($id)) ? $id : false,
			'name' => (isset($name)) ? $name : false,
			'required' => (isset($required)) ? $required : false
		)); ?>
		<?php if($multiple) { ?> multiple="multiple"<?php } ?>
	>
	<?php if(isset($placeholder) && $placeholder) { ?>
	<option value=""><?php echo $placeholder; ?></option>
	<?php } ?>
	<?php foreach($choices as $key => $label) { ?>
	<option value="<?php echo $key; ?>"<?php if($value == $key) {?> selected="selected"<?php } ?>><?php echo $label; ?></option>
	<?php } ?>
	</select>
<?php }else{ ?>
	<?php foreach($choices as $key => $label) { ?>
		<?php if($multiple) { 
			$this->view('Alphapup','Application/View/Voltron/CheckboxTemplate.php',array(),array(
				'attr' => (isset($attr)) ? $attr : false,
				'checked' => ($value === $key) ? true : false,
				'disabled' => (isset($disabled)) ? $disabled : false,
				'id' => (isset($id)) ? $id : false,
				'label' => $label,
				'name' => (isset($name)) ? $name.'[]' : false,
				'required' => (isset($required)) ? $required : false,
				'value' => $key
			));
		}else{ 
			$this->view('Alphapup','Application/View/Voltron/RadioTemplate.php',array(),array(
				'attr' => (isset($attr)) ? $attr : false,
				'checked' => ($value === $key) ? true : false,
				'disabled' => (isset($disabled)) ? $disabled : false,
				'id' => (isset($id)) ? $id : false,
				'label' => $label,
				'name' => (isset($name)) ? $name : false,
				'required' => (isset($required)) ? $required : false,
				'value' => $key
			));
		} ?>
	<?php } ?>
<?php } ?>