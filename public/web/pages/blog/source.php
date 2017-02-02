<?php

class blog
{
	public function showTemplate() {
		include_once _cfg('pages').'/'.get_class().'/index.html';
	}
}