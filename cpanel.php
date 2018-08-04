<?php 
    $flag = true;
    include_once('config.php');
    /* Check password */
    if (isset($_POST['password'])) {
        $flag = false;
        if ($_POST['password'] == $configCpanel['password']) {
            echo 'true';
            setcookie("secure", md5($configCpanel['password']), time() + (86400 * 30), '/');
        }
        else
            echo 'false';
    }
    /* Logout */
    if (isset($_GET['go']) && ($_GET['go'] == "logout")) {
        $flag = false;
        setcookie("secure", "owner", time(), '/');
    }
    /* Something */
    if (isset($_POST['type']) && $_POST['type'] != '') {
        $flag = false;
        if ($_POST['type'] == 'getkey' && isset($_POST['n']) && $_POST['n'] != '' && isset($_POST['p']) && $_POST['p'] != '') {
            $page = _curl($config['cboxUrl'] . '&sec=profile&n=' . $_POST['n'] . '&k=00000000', '', '&pword=' . $_POST['p'] . '&sublog=+Log+in+');
            if (preg_match('/key:\s"(.*?)",/', $page, $matches))
                echo $matches[1];
            else
                echo 'error';
        }
        else if ($_POST['type'] == 'savedata' && isset($_POST['t']) && $_POST['t'] != '' &&  isset($_POST['d']) && $_POST['d'] != '') {
            if ($_POST['t'] == 'config' || $_POST['t'] == 'server' || $_POST['t'] == 'admin' || $_POST['t'] == 'black' || $_POST['t'] == 'badword') {
                if ($_POST['t'] == 'config') {
                    $dataSave = json_decode($_POST['d'], true);
                    $userData = json_decode(_readFile($fileList['config']), true);
                    /* Save array to array */
                    foreach($dataSave as $a => $b) 
                        if (isset($userData[$a])) 
                            if (($a == 'sizeLimited' || $a == 'bandwithLimited') && is_array($b)) {
                                foreach ($b as $c => $d)
                                    if (isset($userData[$a][$c])) 
                                        $userData[$a][$c] = $d;
                            }
                            else 
                                $userData[$a] = $b;
                    /* Save data */
                    _writeFile($fileList[$_POST['t']], json_encode($userData), 'w');        
                }
                else
                     /* Save data */
                    _writeFile($fileList[$_POST['t']], $_POST['d'], 'w');                   
            }
            else 
                echo '404 - Not found.';
        }
        else if ($_POST['type'] == 'readlog' && isset($_POST['d'])) {
            if ($_POST['d'] == 'server') {
                $handle = fopen('log/serverLog.dat', 'r');
                if ($handle) {
                    while (($line = fgets($handle)) !== false) {
                        echo $line . '<br>';
                    }
                    fclose($handle);
                }
            }
        }
        else
            echo '404 - Not found.';
    }
    /* Base */
    if ($flag == true) {
        session_start();
        if (isset($_COOKIE['secure']) && ($_COOKIE['secure'] == md5($configCpanel['password']))) {
        ?>
<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Cpanel</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-icon.png">
        <link rel="shortcut icon" href="http://vnz-leech.com/favicon.ico">
        <link rel="stylesheet" href="assets/css/normalize.css">
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">
        <link rel="stylesheet" href="assets/scss/style.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800" type="text/css">
    </head>
    <body>
        <aside id="left-panel" class="left-panel" style="width: 210px;">
            <nav class="navbar navbar-expand-sm navbar-default">
                <div class="navbar-header">
                    <button class="navbar-toggler" type="button">
                        <i class="fa fa-bars"></i>
                    </button>
                    <br></br>
                </div>
                <div id="main-menu" class="main-menu collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li class="menu-item-has-children active dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-laptop"></i>Starting</a>
                            <ul class="sub-menu children dropdown-menu">
                                <li><i class="fa fa-group"></i><a href="<?php echo $currentUrl . '/member.php'; ?>" target="_blank">Member</a></li>
                                <li><i class="fa fa-heart"></i><a href="<?php echo $currentUrl . '/vip.php'; ?>" target="_blank">Vip</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="cpanel.php"><i class="menu-icon fa fa-dashboard"></i>Dashboard </a>
                        </li>
                        <li>
                            <a href="cpanel.php?go=server"><i class="menu-icon fa fa-laptop"></i>Server </a>
                        </li>
                        <li>
                            <a href="cpanel.php?go=log"><i class="menu-icon fa fa-table"></i>Log </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>
        <div id="right-panel" class="right-panel">
            <header id="header" class="header">
                <div class="header-menu">
                    <div class="col-sm-7">
                        <a id="menuToggle" class="menutoggle pull-left"><i class="fa fa fa-tasks"></i></a>
                        <div class="header-left">
                        </div>
                    </div>
                    <div class="col-sm-5">
                    </div>
                </div>
            </header>
            <?php 
            if (isset($_GET['go'])) {
                if ($_GET['go'] == "server") {
                    ?>
                    <div class="breadcrumbs">
                        <div class="col-sm-4">
                            <div class="page-header float-left">
                                <div class="page-title">
                                    <h1>Server</h1>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="page-header float-right">
                                <div class="page-title">
                                    <ol class="breadcrumb text-right">
                                        <li>Cpanel</li>
                                        <li class="active">Server</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content mt-3">
                        <div class="row">
                            <div class="col col-sm-12 alert" style="display: none;">
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4>Server</h4>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped" style="border: 1px solid #dee2e6;">
                                    <thead>
                                        <th scope="col" style="width: 150px">Host</th>
                                        <th scope="col">Server</th>
                                        <th scope="col" style="width: 100px">Status</th>
                                        <th scope="col" style="width: 100px">Edit</th>
                                    </thead>
                                    <tbody>
                                    <?php
                                        foreach($server as $key => $value) {
                                            echo '<tr>';
                                            /* Column 1 */
                                            echo '<td><input class="form-control" value="' . $key . '"></td>';
                                            /* Column 2 */
                                            echo '<td>';
                                            if (!empty($value['server'])) {
                                                if (count($value['server']) > 1) {
                                                    $flag = true;
                                                    foreach($value['server'] as $item) {
                                                        echo '<div class="row" ' . ($flag == false ? 'style="margin-top: 10px;"' : '') . '><div class="col col-md-10"><input class="form-control" value="' . $item . '"></div><div class="col col-md-2"><button id="removeServer" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                                        $flag = false;
                                                    }
                                                }
                                                else 
                                                    echo '<div class="row"><div class="col col-md-10"><input class="form-control" value="' . $value['server'][0] . '"></div><div class="col col-md-2"><button id="removeServer" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            }
                                            else
                                                echo '<div class="row"><div class="col col-md-10"><input class="form-control" value=""></div><div class="col col-md-2"><button id="removeServer" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            echo '<div style="margin-top: 10px;"><button id="addServer" class="btn btn-sm btn-flat btn-primary"><i class="fa fa-plus"></i>&nbsp;Add </button></div>';
                                            echo '</td>';
                                            /* Column 3 */
                                            echo '<td>';
                                            if ($value['work'] == true)
                                                echo '<div style="margin-top: 5px;"><span class="badge badge-success">Online</span></div>';
                                            else
                                                echo '<div style="margin-top: 5px;"><span class="badge badge-danger">Offline</span></div>';
                                            echo '</td>';
                                            /* Column 4 */
                                            echo '<td><div class="col col-md-2"><button id="removeHost" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></td></tr>';
                                        }
                                        echo '<tr><td><div class="col col-md-2"><button id="addHost" class="btn btn-sm btn-flat btn-primary" style="margin-top: 3px;"><i class="fa fa-plus"></i>&nbsp; Add</button></div></td><td></td><td></td><td></td></tr>';
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer" style="display: none;">
                                <button id="save" class="btn btn-sm btn-flat btn-success">
                                    <i class="fa fa-check"></i>&nbsp;Save
                                </button>
                                <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                    <i class="fa fa-refresh"></i>&nbsp;Reset
                                </button>                                   
                            </div>
                        </div>
                    </div>
                    <script src="assets/js/jquery.min.js"></script>
                    <script src="assets/js/bootstrap.min.js"></script>
                    <script src="assets/js/server.js"></script>
                    <?php
                }
                elseif ($_GET['go'] == "log") {
                    ?>
                    <div class="breadcrumbs">
                        <div class="col-sm-4">
                            <div class="page-header float-left">
                                <div class="page-title">
                                    <h1>Log</h1>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="page-header float-right">
                                <div class="page-title">
                                    <ol class="breadcrumb text-right">
                                        <li>Cpanel</li>
                                        <li class="active">Log</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content mt-3">
                        <div class="card">
                            <div class="card-header">
                                <h4>Server</h4>
                            </div>
                            <div class="card-body">
                                <div class="card" style="margin: 0 auto;">
                                    <div id="serverLog" class="card-body">
                                        <p style="margin: 0 auto;">
                                        <?php
                                            $handle = fopen('log/serverLog.dat', 'r');
                                            if ($handle) {
                                                while (($line = fgets($handle)) !== false) {
                                                    echo $line . '<br>';
                                                }
                                                fclose($handle);
                                            }
                                        ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script src="assets/js/jquery.min.js"></script>
                    <script src="assets/js/bootstrap.min.js"></script>
                    <script language="javascript">
                        jQuery(document).ready(function() {
                            setInterval(function() { 
                                $.ajax({
                                    url: 'cpanel.php',
                                    method: 'POST',
                                    data: 'type=readlog&d=server',
                                    success: function(data) {
                                        $('#serverLog').html('<p style="margin: 0 auto;">' + data + '</p>');
                                    },
                                });
                            }, 10000);
                        });
                    </script>                                   
                    <?php
                }
            }
            else {
                ?>
                <div class="breadcrumbs">
                    <div class="col-sm-4">
                        <div class="page-header float-left">
                            <div class="page-title">
                                <h1>Dashboard</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="page-header float-right">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li>Cpanel</li>
                                    <li class="active">Dashboard</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content mt-3">
                    <div class="row">
                        <div class="col col-sm-12 alert" style="display: none;">
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card" style="margin-bottom: -1px;">
                                    <div class="card-header">
                                        <h4>Status</h4>
                                    </div>
                                    <div class="card-body">
                                        <p style="margin: 0 auto;">
                                            Work&nbsp;&nbsp;<span id="work" class="badge badge-<?php if ($config['work'] == true) {echo 'success">True';} else {echo 'danger">False';} ?></span><br>
                                            Chat&nbsp;&nbsp;<span id="chat" class="badge badge-<?php if ($config['chat'] == true) {echo 'success">True';} else {echo 'danger">False';} ?></span><br>
                                            Zip&nbsp;&nbsp;<span id="zip" class="badge badge-<?php if ($config['zip'] == true) {echo 'success">True';} else {echo 'danger">False';} ?></span><br>
                                            Check3x&nbsp;&nbsp;<span id="check3x" class="badge badge-<?php if ($config['check3x'] == true) {echo 'success">True';} else {echo 'danger">False';} ?></span><br>
                                            Error&nbsp;&nbsp;<span class="badge badge-danger">0</span><br>
                                            Login as&nbsp;&nbsp;<span id="userBot" class="badge badge-warning"><?php echo $config['bot']['name']; ?></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card" style="margin-bottom: 10px;">
                                    <div class="card-header">
                                        <h4>Url</h4>
                                    </div>
                                    <div class="card-body">
                                        <p style="margin: 0 auto;">
                                            Cbox&nbsp;&nbsp;<span id="cboxUrl" class="badge badge-light"><?php echo $config['cboxUrl']; ?></span><br>
                                            Check&nbsp;&nbsp;<span id="checkUrl" class="badge badge-info"><?php echo $config['checkUrl']; ?></span><br>
                                            Zip&nbsp;&nbsp;<span id="zipUrl" class="badge badge-dark"><?php echo $config['zipUrl']; ?></span>
                                        </p>
                                    </div>
                                </div> 
                                <div class="card" style="margin-bottom: -1px;">
                                    <div class="card-body">
                                        <p style="margin: 0 auto;">
                                            List admin&nbsp;&nbsp;<span id="admin" class="badge badge-primary">Here</span>&nbsp;&nbsp;&nbsp;List bad word&nbsp;&nbsp;<span id="badword" class="badge badge-warning">Here</span>&nbsp;&nbsp;&nbsp;List parameter check&nbsp;&nbsp;<span id="parameterCheck" class="badge badge-dark">Here</span>
                                        </p>
                                    </div>
                                </div>
                            </div>                                                      
                            <div class="col-md-12">
                                <div id="bot" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div>
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>
                                </div>
                                <div id="userBot" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div class="row">
                                        <div class="col col-sm-4">
                                            <input id="nameBot" type="text" class="form-control" value="<?php echo $config['bot']['name']; ?>">
                                        </div>
                                        <div class="col col-sm-4">
                                            <input id="keyBot" type="text" class="form-control" value="<?php echo $config['bot']['key']; ?>">
                                        </div>
                                    </div>
                                    <div id="getKey" class="row" style="margin-top: 5px; display: none;">
                                        <div class="col col-sm-4">
                                            <input id="passBot" type="text" class="form-control" value="">
                                        </div>
                                        <div class="col col-sm-4">
                                            <button id="run" class="btn btn-sm btn-flat btn-primary" style="margin-top: 3px;">
                                                <i class="fa fa-terminal"></i>&nbsp;Run
                                            </button>
                                        </div>                                    
                                    </div>
                                    <div style="margin-top: 10px;">
                                        <button id="get" class="btn btn-sm btn-flat btn-primary" style="margin-top: 3px;">
                                            <i class="fa fa-plus"></i>&nbsp;Get key
                                        </button>
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>
                                </div>
                                <div id="cboxUrl" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div class="row">
                                        <div class="col col-sm-8">
                                            <input id="cboxUrl" type="text" class="form-control" value="<?php echo $config['cboxUrl']; ?>">
                                        </div>
                                    </div>                                
                                    <div style="margin-top: 10px;">
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>                                
                                </div>
                                <div id="checkUrl" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div class="row">
                                        <div class="col col-sm-8">
                                            <input id="checkUrl" type="text" class="form-control" value="<?php echo $config['checkUrl']; ?>">
                                        </div>
                                    </div>                                
                                    <div style="margin-top: 10px;">
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>                                
                                </div> 
                                <div id="zipUrl" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div class="row">
                                        <div class="col col-sm-8">
                                            <input id="zipUrl" type="text" class="form-control" value="<?php echo $config['zipUrl']; ?>">
                                        </div>
                                    </div>                                
                                    <div style="margin-top: 10px;">
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>                                
                                </div>
                                <div id="admin" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div id="adminList">
                                    <?php
                                        $flag = true; 
                                        foreach($adminList as $value) 
                                            if ($flag) {
                                                $flag = false;
                                                echo '<div class="row"><div class="col col-sm-4"><input type="text" class="form-control" value="' . $value . '"></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            }
                                            else 
                                                echo '<div class="row" style="margin-top: 10px;"><div class="col col-sm-4"><input type="text" class="form-control" value="' . $value . '"></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                    ?>                                         
                                    </div>                              
                                    <div style="margin-top: 10px;">
                                        <button id="add" class="btn btn-sm btn-flat btn-primary">
                                            <i class="fa fa-plus"></i>&nbsp;Add
                                        </button>                                        
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>                                
                                </div>
                                <div id="badword" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div id="badwordList">
                                    <?php
                                        $flag = true; 
                                        foreach($badwordList as $value) 
                                            if ($flag) {
                                                $flag = false;
                                                echo '<div class="row"><div class="col col-sm-4"><input type="text" class="form-control" value="' . $value . '"></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            }
                                            else 
                                                echo '<div class="row" style="margin-top: 10px;"><div class="col col-sm-4"><input type="text" class="form-control" value="' . $value . '"></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                    ?>                                         
                                    </div>                              
                                    <div style="margin-top: 10px;">
                                        <button id="add" class="btn btn-sm btn-flat btn-primary">
                                            <i class="fa fa-plus"></i>&nbsp;Add
                                        </button>                                        
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>                                
                                </div>
                                <div id="parameterCheck" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div id="parameterCheckList">
                                    <?php
                                        $flag = true; 
                                        foreach($config['parameterCheck'] as $value) 
                                            if ($flag == true) {
                                                $flag = false;
                                                echo '<div class="row"><div class="col col-sm-4"><input type="text" class="form-control" value="' . $value . '"></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            }
                                            else 
                                                echo '<div class="row" style="margin-top: 10px;"><div class="col col-sm-4"><input type="text" class="form-control" value="' . $value . '"></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                    ?> 
                                    </div>                              
                                    <div style="margin-top: 10px;">
                                        <button id="add" class="btn btn-sm btn-flat btn-primary">
                                            <i class="fa fa-plus"></i>&nbsp;Add
                                        </button>                                        
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>                                 
                                </div>                                                          
                            </div>                        
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card" style="border-bottom: 0px; margin-bottom: 5px;">
                                    <div class="card-header">
                                        <h4>For member</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card" style="margin-bottom: -1px;">
                                    <div class="card-header">
                                        <h4>Host</h4>
                                    </div>
                                    <div class="card-body">
                                        <p style="margin: 0px;">
                                        Total <span class="badge badge-primary"><?php echo count($config['hostLimited']); ?></span>&nbsp;<span id="hostLimited" class="badge badge-dark">Edit</span><br>
                                        <?php
                                            foreach($config['hostLimited'] as $value) {
                                                echo "<img src=\"https://www.google.com/s2/favicons?domain={$value}\">&nbsp;{$value}<br>";
                                            }
                                        ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card" style="margin-bottom: -1px;">
                                    <div class="card-header">
                                        <h4>Size</h4>
                                    </div>
                                    <div class="card-body">
                                        <p style="margin: 0px;">
                                        Total <span class="badge badge-success"><?php echo count($config['sizeLimited']['member']); ?></span>&nbsp;<span id="sizeLimited" class="badge badge-dark">Edit</span><br>
                                        <?php
                                            foreach($config['sizeLimited']['member'] as $key => $value) {
                                                if ($key == "default")
                                                    echo "<img src=\"http://vnz-leech.com/favicon.ico\">&nbsp;{$key}&nbsp;<span class=\"badge badge-light\">" . _reconvertSize($value['vn']) . "</span>&nbsp;<span class=\"badge badge-dark\">" . _reconvertSize($value['us']) . "</span><br>";
                                                else
                                                    echo "<img src=\"https://www.google.com/s2/favicons?domain={$key}\">&nbsp;{$key}&nbsp;<span class=\"badge badge-dark\">" . _reconvertSize($value) . "</span><br>";
                                            }
                                        ?>
                                        </p>                                        
                                    </div>                                    
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card" style="margin-bottom: -1px;">
                                    <div class="card-header">
                                        <h4>Bandwith</h4>
                                    </div>
                                    <div class="card-body">
                                        <p style="margin: 0px;">
                                        Total <span class="badge badge-danger"><?php echo count($config['bandwithLimited']['member']); ?></span>&nbsp;<span id="bandwithLimited" class="badge badge-dark">Edit</span><br>
                                        <?php
                                            foreach($config['bandwithLimited']['member'] as $key => $value) {
                                                if ($key == "default")
                                                    echo "<img src=\"http://vnz-leech.com/favicon.ico\">&nbsp;{$key}&nbsp;<span class=\"badge badge-dark\">" . _reconvertSize($value) . "</span><br>";
                                                else
                                                    echo "<img src=\"https://www.google.com/s2/favicons?domain={$key}\">&nbsp;{$key}&nbsp;<span class=\"badge badge-dark\">" . _reconvertSize($value) . "</span><br>";
                                            }
                                        ?>
                                        </p>                                         
                                    </div>                                    
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div id="hostLimited" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div id="hostLimitedList">
                                    <?php
                                        $flag = true; 
                                        foreach($config['hostLimited'] as $value) 
                                            if ($flag == true) {
                                                $flag = false;
                                                echo '<div class="row"><div class="col col-sm-4"><input type="text" class="form-control" value="' . $value . '"></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            }
                                            else 
                                                echo '<div class="row" style="margin-top: 10px;"><div class="col col-sm-4"><input type="text" class="form-control" value="' . $value . '"></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                    ?> 
                                    </div>                              
                                    <div style="margin-top: 10px;">
                                        <button id="add" class="btn btn-sm btn-flat btn-primary">
                                            <i class="fa fa-plus"></i>&nbsp;Add
                                        </button>                                        
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>                                 
                                </div> 
                                <div id="sizeLimited" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div id="sizeLimitedList">
                                    <?php
                                        $flag = true; 
                                        foreach($config['sizeLimited']['member'] as $key => $value) {
                                            $random = rand();
                                            if ($flag == true) {
                                                $flag = false;
                                                if ($key == "default")
                                                    echo '<div class="row"><div class="col col-sm-3"><input id="default" type="text" class="form-control" value="' . $key . '" disabled></div><div class="col col-sm-2"><input id="vn" type="text" class="form-control" value="' . _reconvertSize($value['vn']) . '"></div><div class="col col-sm-2"><input id="us" type="text" class="form-control" value="' . _reconvertSize($value['us']) . '"></div></div>';
                                                else    
                                                    echo '<div class="row"><div class="col col-sm-3"><input id="' . $random . '" type="text" class="form-control" value="' . $key . '"></div><div class="col col-sm-2"><input name="' . $random . '" type="text" class="form-control" value="' . _reconvertSize($value) . '"></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            }
                                            else {
                                                if ($key == "default")
                                                    echo '<div class="row" style="margin-top: 10px"><div class="col col-sm-3"><input id="default" type="text" class="form-control" value="' . $key . '" disabled></div><div class="col col-sm-2"><input id="vn" type="text" class="form-control" value="' . _reconvertSize($value['vn']) . '"></div><div class="col col-sm-2"><input id="us" type="text" class="form-control" value="' . _reconvertSize($value['us']) . '"></div></div>';
                                                else 
                                                    echo '<div class="row" style="margin-top: 10px;"><div class="col col-sm-3"><input id="' . $random . '" type="text" class="form-control" value="' . $key . '"></div><div class="col col-sm-2"><input name="' . $random . '" type="text" class="form-control" value="' . _reconvertSize($value) . '"></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            }
                                        }
                                    ?> 
                                    </div>                              
                                    <div style="margin-top: 10px;">
                                        <button id="add" class="btn btn-sm btn-flat btn-primary">
                                            <i class="fa fa-plus"></i>&nbsp;Add
                                        </button>                                        
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>                                 
                                </div>
                                <div id="bandwithLimited" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div id="bandwithLimitedList">
                                    <?php
                                        $flag = true; 
                                        foreach($config['bandwithLimited']['member'] as $key => $value) {
                                            $random = rand();
                                            if ($flag == true) {
                                                $flag = false;
                                                if ($key == "default")
                                                    echo '<div class="row"><div class="col col-sm-3"><input id="default" type="text" class="form-control" value="' . $key . '" disabled></div><div class="col col-sm-2"><input name="default" type="text" class="form-control" value="' . _reconvertSize($value) . '"></div></div>';
                                                else    
                                                    echo '<div class="row"><div class="col col-sm-3"><input id="' . $random . '" type="text" class="form-control" value="' . $key . '"></div><div class="col col-sm-2"><input name="' . $random . '" type="text" class="form-control" value="' . _reconvertSize($value) . '"></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            }
                                            else {
                                                if ($key == "default")
                                                    echo '<div class="row" style="margin-top: 10px"><div class="col col-sm-3"><input id="default" type="text" class="form-control" value="' . $key . '" disabled></div><div class="col col-sm-2"><input name="default" type="text" class="form-control" value="' . _reconvertSize($value) . '"></div></div>';
                                                else 
                                                    echo '<div class="row" style="margin-top: 10px;"><div class="col col-sm-3"><input id="' . $random . '" type="text" class="form-control" value="' . $key . '"></div><div class="col col-sm-2"><input name="' . $random . '" type="text" class="form-control" value="' . _reconvertSize($value) . '"></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            }
                                        }
                                    ?> 
                                    </div>                              
                                    <div style="margin-top: 10px;">
                                        <button id="add" class="btn btn-sm btn-flat btn-primary">
                                            <i class="fa fa-plus"></i>&nbsp;Add
                                        </button>                                        
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>                                 
                                </div>                                                                      
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card" style="border-bottom: 0px; margin-bottom: 5px;">
                                    <div class="card-header">
                                        <h4>For vip</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card" style="margin-bottom: -1px;">
                                    <div class="card-header">
                                        <h4>Size</h4>
                                    </div>
                                    <div class="card-body">
                                        <p style="margin: 0px;">
                                        Total <span class="badge badge-success"><?php echo count($config['sizeLimited']['vip']); ?></span>&nbsp;<span id="sizeVipLimited" class="badge badge-dark">Edit</span><br>
                                        <?php
                                            foreach($config['sizeLimited']['vip'] as $key => $value) {
                                                echo "<img src=\"https://www.google.com/s2/favicons?domain={$key}\">&nbsp;{$key}&nbsp;<span class=\"badge badge-dark\">" . _reconvertSize($value) . "</span><br>";
                                            }
                                        ?>
                                        </p>                                         
                                    </div>                                    
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card" style="margin-bottom: -1px;">
                                    <div class="card-header">
                                        <h4>Bandwith</h4>
                                    </div>
                                    <div class="card-body">
                                        <p style="margin: 0px;">
                                        Total <span class="badge badge-danger"><?php echo count($config['bandwithLimited']['vip']); ?></span>&nbsp;<span id="bandwithVipLimited" class="badge badge-dark">Edit</span><br>
                                        <?php
                                            foreach($config['bandwithLimited']['vip'] as $key => $value) {
                                                echo "<img src=\"https://www.google.com/s2/favicons?domain={$key}\">&nbsp;{$key}&nbsp;<span class=\"badge badge-dark\">" . _reconvertSize($value) . "</span><br>";
                                            }
                                        ?>
                                        </p>                                        
                                    </div>                                    
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div id="sizeVipLimited" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div id="sizeVipLimitedList">
                                    <?php
                                        if (!empty($config['sizeLimited']['vip'])) {
                                            $flag = true;
                                            foreach($config['sizeLimited']['vip'] as $key => $value) {
                                                $random = rand();
                                                if ($flag == true) {
                                                    $flag = false; 
                                                    echo '<div class="row"><div class="col col-sm-3"><input id="' . $random . '" type="text" class="form-control" value="' . $key . '"></div><div class="col col-sm-2"><input name="' . $random . '" type="text" class="form-control" value="' . _reconvertSize($value) . '"></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';                                    
                                                }
                                                else 
                                                    echo '<div class="row" style="margin-top: 10px;"><div class="col col-sm-3"><input id="' . $random . '" type="text" class="form-control" value="' . $key . '"></div><div class="col col-sm-2"><input name="' . $random . '" type="text" class="form-control" value="' . _reconvertSize($value) . '"></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            }
                                        }
                                    ?> 
                                    </div>                              
                                    <div style="margin-top: 10px;">
                                        <button id="add" class="btn btn-sm btn-flat btn-primary">
                                            <i class="fa fa-plus"></i>&nbsp;Add
                                        </button>                                        
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>                                 
                                </div>
                                <div id="bandwithVipLimited" class="card-footer" style="border: 1px solid rgba(0, 0, 0, .125); background-color: #f0f3f5; display: none;">
                                    <div id="bandwithVipLimitedList">
                                    <?php
                                        if (!empty($config['bandwithLimited']['vip'])) {
                                            $flag = true; 
                                            foreach($config['bandwithLimited']['vip'] as $key => $value) {
                                                $random = rand();
                                                if ($flag == true) {
                                                    $flag = false; 
                                                    echo '<div class="row"><div class="col col-sm-3"><input id="' . $random . '" type="text" class="form-control" value="' . $key . '"></div><div class="col col-sm-2"><input name="' . $random . '" type="text" class="form-control" value="' . _reconvertSize($value) . '"></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                                }
                                                else
                                                    echo '<div class="row" style="margin-top: 10px;"><div class="col col-sm-3"><input id="' . $random . '" type="text" class="form-control" value="' . $key . '"></div><div class="col col-sm-2"><input name="' . $random . '" type="text" class="form-control" value="' . _reconvertSize($value) . '"></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>';
                                            }
                                        }
                                    ?> 
                                    </div>                              
                                    <div style="margin-top: 10px;">
                                        <button id="add" class="btn btn-sm btn-flat btn-primary">
                                            <i class="fa fa-plus"></i>&nbsp;Add
                                        </button>                                        
                                        <button id="save" class="btn btn-sm btn-flat btn-success">
                                            <i class="fa fa-check"></i>&nbsp;Save
                                        </button>
                                        <button id="reset" class="btn btn-sm btn-flat btn-danger">
                                            <i class="fa fa-refresh"></i>&nbsp;Reset
                                        </button>
                                    </div>                                 
                                </div>  
                            </div>
                        </div>                        
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                <h4>Host</h4>
                            </div>
                            <div class="card-body">
                                <p style="margin: 0;">
                                    <span class="badge badge-success">Online</span>&nbsp;<span class="badge badge-primary"><?php echo count($hostOnline); ?></span><br>
                                    <?php
                                        foreach($hostOnline as $value)
                                            echo "<img src=\"https://www.google.com/s2/favicons?domain={$value}\">&nbsp;{$value}<br>";
                                    ?>
                                </p>    
                                <hr>
                                <p style="margin: 0;">
                                    <span class="badge badge-danger">Offline</span>&nbsp;<span class="badge badge-primary"><?php echo count($hostOffline); ?></span><br>
                                    <?php
                                        foreach($hostOffline as $value)
                                            echo "<img src=\"https://www.google.com/s2/favicons?domain={$value}\">&nbsp;{$value}<br>";
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>        
                </div>
                <script src="assets/js/jquery.min.js"></script>
                <script src="assets/js/bootstrap.min.js"></script>
                <script src="assets/js/dashboard.js"></script>               
                <?php
            }
            ?>
    </body>
</html>
            <?php
        }
        else {
            ?>
<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Login</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="http://vnz-leech.com/favicon.ico">
        <link rel="stylesheet" href="assets/css/normalize.css">
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">
        <link rel="stylesheet" href="assets/scss/style.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800" type="text/css">
    </head>
    <body class="bg-dark">
        <div class="align-content-center d-flex flex-wrap">
            <div class="container">
                <div class="login-content">
                    <div class="login-logo" style="width: 540px; height: 50px;">
                        <div class="alert" align="left" style="display: none;">
                        </div>
                    </div>
                    <div class="login-form">
                        <div class="form-group">
                            <label>Password</label>
                            <input id="password" type="password" class="form-control" placeholder="Password">
                        </div>
                        <button type="submit" class="btn btn-success btn-flat m-b-30 m-t-30">Sign in</button>
                    </div>
                </div>
            </div>
        </div>
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/login.js"></script>
    </body>
</html>
            <?php
        }
    }
?>