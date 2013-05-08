<?php $this->view('Alphapup','Application/View/Profiler/Profile/Header.php'); ?>

<div id="profiler_content" class="clearfix">

	<?php $this->view('Alphapup','Application/View/Profiler/Profile/Navigation.php'); ?>

	<div class="content">
		<h2>Configuration</h2>
		
		<?php foreach($profile->collector('config')->toArray() as $section => $config) { ?>
		<h3><?php echo $section; ?></h3>
		<pre>
			<?php print_r($config); ?>
		</pre>
		<?php } ?>
	</div>
</div>