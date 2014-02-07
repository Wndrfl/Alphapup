<?php $this->view('Alphapup','Application/View/Profiler/Profile/Header.php'); ?>

<div id="profiler_content" class="clearfix">

	<?php $this->view('Alphapup','Application/View/Profiler/Profile/Navigation.php'); ?>

	<div class="content">
		<h2>Request</h2>

		<h3>Routing</h3>
		<table class="data">
			<tr>
				<td class="header">Controller</td>
				<td><?php echo $profile->collector('request')->controllerName(); ?></td>
			</tr>
			<tr>
				<td class="header">Action</td>
				<td><?php echo $profile->collector('request')->actionName(); ?></td>
			</tr>
		</table>
		
		<h3>Headers</h3>
		<table class="data">
			<?php foreach($profile->collector('request')->headers() as $header => $value) { ?>
			<tr>
				<td class="header">
					<?php echo $header; ?>
				</td>
				<td>
					<?php echo $value; ?>
				</td>
			</tr>
			<?php } ?>
		</table>
	</div>
</div>