<?php $this->view('Alphapup','Application/View/Profiler/Profile/Header.php'); ?>

<div id="profiler_content" class="clearfix">

	<?php $this->view('Alphapup','Application/View/Profiler/Profile/Navigation.php'); ?>

	<div class="content">
		<h2>Dexter</h2>
		
		<table class="data">
			<tr>
				<td class="header">Total Queries</td>
				<td><?php echo $totalQueries; ?></td>
			</tr>
			<tr>
				<td class="header">Total Query Time</td>
				<td><?php echo $totalQueryTime; ?> seconds</td>
			</tr>
		</table>
		
		<h3>Queries</h3>
		<ul class="zebra">
			<?php foreach($profile->collector('dexter')->queries() as $query) { ?>
			<li class="clearfix">
				<div class="left">
					<?php echo $query['sql']; ?>
				</div>
				<div class="right">
					[<?php echo $query['totalTime']; ?> sec.][<?php echo $query['rowCount']; ?> rows]
				</div>
			</li>	
			<?php } ?>
		</ul>
	</div>
</div>