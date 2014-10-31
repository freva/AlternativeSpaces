<?php
$path = substr(realpath("."), 0, strpos(realpath("."), "/source")+7) . "/";
include_once($path . "backend/db/DBVotes.php");


function getVoter($database, $id, $up, $down) {
    $status = didVote(substr($database, 0, 1) . $id);
    $sum = $up + $down;
    $likesPercent = ($sum == 0 ? 100 : 100.0*$up/$sum);

    return '<div data-voter-id="'.$id.'" data-voter-db="'.$database.'">
    <div class="likeBar"><div class="likeBarLikes" style="width:'.$likesPercent.'%"></div></div>
    <button type="button" onclick="vote(1, '.$id.', \''.$database.'\')" class="likeButton" '.($status == 1 || !isLoggedIn() ? "disabled" : "").'></button>'.$up.'
    <button type="button" onclick="vote(0, '.$id.', \''.$database.'\')" class="dislikeButton" '.($status == -1 || !isLoggedIn() ? "disabled" : "").'></button>'.$down.'
    </div>';
}


if(isset($_GET["cid"]) && isset($_GET["db"]) && isset($_GET["vote"])) {
    if(isLoggedIn()) {
        $database = mysql_real_escape_string($_GET["db"]);
        if (!is_numeric($_GET["cid"])) return;
        else $id = $_GET["cid"];
        if (!in_array($_GET["vote"], array("0", "1"))) return;
        $cid = substr($database, 0, 1) . $id;
        $vote = intval($_GET["vote"]);

        $status = didVote($cid);
        registerVote($cid, $vote);

        if ($status == 0) {
            $up = $vote;
            $down = 1 - $vote;
        } else {
            $up = -$status;
            $down = $status;
        }

        if ($database == "photos") {
            include_once($path . "backend/db/DBPhotos.php");
            list($up, $down) = votePhoto($id, $up, $down);
        } else if ($database == "events") {
            include_once($path . "backend/db/DBEvents.php");
            list($up, $down) = voteEvent($id, $up, $down);
        }
    }

    $response = array("success" => true, "response" => getVoter($database, $id, $up, $down));
    echo json_encode($response);
}

?>