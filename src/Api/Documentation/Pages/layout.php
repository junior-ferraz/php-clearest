<?php
    /** @var \CleaRest\Api\Documentation\Generator $this */
?>
<html>
<head>
    <title><?=$this->title;?></title>
    <?php
        foreach ($this->getScripts() as $path) {
            print '<script type="text/javascript">' . file_get_contents($path) . '</script>';
        }
        foreach ($this->getCssFiles() as $path) {
            print '<style type="text/css">' . file_get_contents($path) . '</style>';
        }
    ?>
</head>
<body>
<div class="layout">
    <div class="header">
        <div class="content">
            <?=$this->title;?>
        </div>
    </div>
    <div class="content">
        <div class="welcome-text">
            Bellow you can see the routes available for this API.<br/>
            Click on one of the methods at the right to open its documentation.
        </div>
        <?php
        /** @var \CleaRest\Api\Route $route */
        $routes = $this->router->getAllRoutes();
        foreach ($routes as $route) {
            ?>
            <div class="route">
                <div class="header">
                    <div class="tabs">
                        <?php foreach ($route->getAllMethods() as $method) {?>
                            <div class="tab <?=$method->getName();?>" data-method="<?=$method->getName();?>"><?= $method->getName(); ?></div>
                        <?php } ?>
                    </div>
                    <div class="path">
                        <?php
                        $routeName = $route->getRoute();
                        foreach ($route->match($routeName) as $key => $value) {
                            $routeName = str_replace($value, '<span class="param">' . $key . '</span>', $routeName);
                        }
                        print $routeName;
                        ?>
                    </div>
                    <div style="clear: both"></div>
                </div>

                <div class="methods">
                    <?php
                    $methods = $route->getAllMethods();
                    foreach ($methods as $method) {
                        $class = \CleaRest\Metadata\MetadataStorage::getClassMetadata($method->getInterfaceName());
                        $metadata = $class->methods[$method->getInterfaceMethod()];
                        $fields = $this->getFields($metadata);
                        $headers = $this->getRequestHeaders($metadata);
                        $errors = $this->getResponseErrors($metadata);
                        $requestBody = $this->getRequestBody($metadata);
                        $responseBody = $this->getResponseBody($metadata);
                        ?>
                        <div class="method <?=$method->getName();?>">
                            <div class="description"><?=$metadata->description;?></div>
                            <hr/>
                            <div class="request">
                                <h1>Request</h1>
                                <?php if (!empty($headers)) { ?>
                                    <h2>Headers</h2>
                                    <table class="headers">
                                        <?php foreach ($headers as $name => $description) { ?>
                                            <tr>
                                                <td class="name"><?=$name;?></td>
                                                <td class="description"><?=$description;?></td>
                                            </tr>
                                        <?php } ?>
                                    </table>
                                <?php } ?>
                                <?php if(!empty($fields)) { ?>
                                    <h2>Fields</h2>
                                    <table class="fields">
                                    <?php foreach ($fields as $field) { ?>
                                        <tr class="field">
                                            <td class="name"><?=$field['name'];?></td>
                                            <td class="type"><?=$field['type'];?></td>
                                            <td class="text"><?=$field['text'];?></td>
                                        </tr>
                                    <?php } ?>
                                    </table>
                                <?php } ?>
                                <?php if($requestBody !== null) { ?>
                                    <h2>Body</h2>
                                    <div class="body"><?=$requestBody;?></div>
                                <?php } ?>
                            </div>
                            <hr/>
                            <div class="response">
                                <h1>Response</h1>
                                <?php if (!empty($errors)) { ?>
                                    <h2>Errors</h2>
                                    <table class="errors">
                                        <?php foreach ($errors as $error) { ?>
                                            <tr>
                                                <td class="code"><?=$error['code'];?></td>
                                                <td class="description"><?=$error['description'];?></td>
                                            </tr>
                                        <?php } ?>
                                    </table>
                                <?php } ?>
                                <?php if ($responseBody !== null) { ?>
                                    <h2>Body</h2>
                                    <div class="body"><?=$responseBody;?></div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>
