<?php
namespace Alphapup\Component\Asset;

interface AssetInterface
{
	public function content();
	public function extension();
	public function path();
}