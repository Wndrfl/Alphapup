<style>
.alphapup-profiler-toolbar {
	border-top:1px #E2E2E2 solid;
	background: #f4f4f4; /* Old browsers */
	background: -moz-linear-gradient(top,  #f4f4f4 0%, #ffffff 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f4f4f4), color-stop(100%,#ffffff)); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  #f4f4f4 0%,#ffffff 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  #f4f4f4 0%,#ffffff 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  #f4f4f4 0%,#ffffff 100%); /* IE10+ */
	background: linear-gradient(to bottom,  #f4f4f4 0%,#ffffff 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f4f4f4', endColorstr='#ffffff',GradientType=0 ); /* IE6-9 */
	width:100%;
	height:50px;
	position:fixed;
	bottom:0;
	left:0;
}

.alphapup-profiler-toolbar-item {
	font-size:14px;
	line-height:20px;
	height:20px;
	padding:15px 10px;
	float:left;
}
</style>

<div id="<?php echo $profileId; ?>" class="alphapup-profiler-toolbar">
	<div class="alphapup-profiler-toolbar-item dexter">
		Queries: <?php echo intval($totalQueries); ?>
	</div>
	<div class="alphapup-profiler-toolbar-item controller">
		<?php echo $controllerName; ?>::<?php echo $actionName; ?>
	</div>
</div>