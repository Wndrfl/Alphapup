<div class="error">
	<h1>Uncaught Exception</h1>
	<div class="exception">
		<div class="exception_message">
			<?php echo $exception->getMessage(); ?>
		</div>
		<div class="exception_meta">
			<?php echo $exception->getFile(); ?> [line <?php echo $exception->getLine(); ?>]
		</div>
		<table class="trace">
			<tr>
				<th>File</th>
				<th>Line</th>
				<th>Class</th>
				<th>Function</th>
			</tr>
			<?php foreach($exception->getTrace() as $trace) { ?>
			<tr>
				<td>
					<?php echo (isset($trace['file'])) ? $trace['file'] : '<span class="null">null</span>'; ?>
				</td>
				<td>
					<?php echo (isset($trace['line'])) ? $trace['line'] : '<span class="null">null</span>'; ?>
				</td>
				<td>
					<?php echo (isset($trace['class'])) ? $trace['class'] : '<span class="null">null</span>'; ?>
				</td>
				<td>
					<?php echo $trace['function']; ?>
				</td>
			</tr>
			<?php } ?>
		</table>
	</div>
</div>