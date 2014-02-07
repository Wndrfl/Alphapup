<?php echo $this->doctype(); ?>
<html> 
<head> 
<title><?php echo $this->title(); ?></title>
<?php foreach($this->links() as $link) { ?>
<link rel="<?php echo $link['rel']; ?>" type="<?php echo $link['type']; ?>" href="<?php echo $link['href']; ?>">
<?php } ?>
<?php foreach($this->scripts('top') as $script) { ?>
<script type="text/javascript" src="<?php echo $script; ?>"></script>
<?php } ?>
<?php foreach($this->metas() as $meta) { ?>
<meta<?php 
if(isset($meta['http-equiv'])) { echo ' http-equiv="'.$meta['http-equiv'].'"';} 
if(isset($meta['name'])) { echo ' name="'.$meta['name'].'"';}
if(isset($meta['content'])) { echo ' content="'.$this->escape($meta['content']).'"';} ?>>
<?php } ?>
<?php foreach($this->inlineScripts('top') as $script) { ?>
<script<?php if($this->htmlVersion() != 5) { echo ' type="text/javascript"';} ?>>
<?php echo $script; ?>
</script>
<?php } ?>
</head> 
<body> 

<?php echo $this->body(); ?>

<?php foreach($this->scripts('bottom') as $script) { ?>
<script type='text/javascript' src='<?php echo $script; ?>'></script>
<?php } ?> 
<?php foreach($this->inlineScripts('bottom') as $script) { ?>
<script<?php if($this->htmlVersion() != 5) { echo ' type=\'text/javascript\'';} ?>>
<?php echo $script; ?>
</script>
<?php } ?>
</body> 
</html>