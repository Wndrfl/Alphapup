<div id="profiler_header">
	<h1>Profiles</h1>
</div>
<div id="profiler_content" class="clearfix">
	<div class="content">
		<ul class="zebra">
		<?php foreach($profiles as $id => $profile) { ?>
			<li class="clearfix">
				<div class="left">
					<a href="<?php echo $this['url']->url('alphapup.profiler.profile',array('id' => $id)); ?>"><?php echo $profile->url(); ?></a>
				</div>
				<div class="right">
					<?php echo date("m-d-Y h:i:s A",$profile->time()); ?>
				</div>
			</li>
		<?php } ?>	
		</ul>
	</div>
</div>