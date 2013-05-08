<ul class="side_nav">
	<li><a href="<?php echo $this['url']->url('alphapup.profiler.profile',array('id'=>$profile->id())); ?>">Request</a></li>
	<li><a href="<?php echo $this['url']->url('alphapup.profiler.profile.events',array('id'=>$profile->id())); ?>">Events</a></li>
	<li><a href="<?php echo $this['url']->url('alphapup.profiler.profile.config',array('id'=>$profile->id())); ?>">Config</a></li>
	<li><a href="<?php echo $this['url']->url('alphapup.profiler.profile.dexter',array('id'=>$profile->id())); ?>">Dexter</a></li>
</ul>