<?php
require 'Leafiagram.php';

$ig = new Leafiagram("","");

$ig->login();


// Japanのタグがついた投稿をサーチしてハートを付け続けるよ
$count = 0;
while(true){
	$res = $ig->tagsearch("Japan");
	foreach ($res["items"] as $post) {
		$count++;
		echo "{$count} : {$post['id']} ->　";
		if($post["has_liked"]) {
			echo "Already liked\n";
		} else {
			$res = $ig->addLike($post["id"]);
			if($res["status"] == "fail"){
				echo "{$res['message']}\n";
			} else {
				echo "OK\n";
			}
			sleep(1); // 規制回避
		}
	}
}