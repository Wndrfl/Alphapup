<?php $this->view('Alphapup','Application/View/Profiler/Profile/Header.php'); ?>

<div id="profiler_content" class="clearfix">

	<?php $this->view('Alphapup','Application/View/Profiler/Profile/Navigation.php'); ?>

	<div class="content">
		<h2>Events</h2>
		<h3>Fired</h3>
		<ul class="zebra">
			<?php foreach($profile->collector('events')->fired() as $event) { ?>
			<li class="clearfix">
				<div class="left">
					<?php echo $event->name(); ?> <?php echo $event->description(); ?>
				</div>
				<div class="right">
					<?php echo date('m-d-Y h:i:s A',$event->timestamp()); ?>
				</div>
			</li>	
			<?php } ?>
		</ul>

		<h3>Not Fired</h3>
		<ul class="zebra">
			<?php foreach($profile->collector('events')->notFired() as $event) { ?>
			<li>
				<?php echo $event; ?>
			</li>	
			<?php } ?>
		</ul>
	</div>
</div>