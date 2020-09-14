<?php
namespace Bootstrap;
use http\Env;
use Nette\Loaders\RobotLoader;
use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Colors;
use splitbrain\phpcli\Options;
use Procedures;
use splitbrain\phpcli\TableFormatter;

/***
 * Class CommandLine
 */
class CommandLine extends CLI {
    private function _listClass():?array
    {
        $load = new RobotLoader();
        $load->addDirectory(__DIR__."/../Procedures");
        $load->ignoreDirs = ["Init.php"];
        $load->rebuild();
        return $this->_reformatClass(array_keys($load->getIndexedClasses()));
    }

    private function _reformatClass(array $data)
    {
        $res = [];
        foreach ($data as $index => $datum) {
            $class = new $datum();
            $split = explode("\\",$datum);
            $res[] = [
                "apiGroup"=>$split[1],
                "procedure"=>$split[2],
                "info"=>$class->info,
                "description"=>$class->description,
            ];
        }
        return $res;

    }

    private function _log($res):void
    {
        if (is_array($res) || is_object($res)){
            $res = json_encode($res);
        }
        $this->info("Debug : ".$res);
    }



    protected function setup(Options $options)
    {
        $options->setHelp('Pipelined API Tests');
        $options->registerOption('version', 'print version', 'v');
        $options->registerOption('lists', 'List All Procedure', 'l');
        $options->registerOption('tests', 'Tests ALL Procedure', 'a');
        $options->registerOption('test-group', 'Tests Group Procedure ', 'g');
        $options->registerOption('test', 'Test Single Procedure ', 't');
    }

    protected function main(Options $options)
    {
        $args = $options->getArgs();;
        if ($options->getOpt('version')) {
            $this->info('0.1.0 ');
        }elseif ($options->getOpt('lists')){
            $tf = new TableFormatter($this->colors);
            $tf->setBorder(' | '); // nice border between colmns

            // show a header
            echo $tf->format(
                array('10%', '20%', '20%','20%','30%'),
                array('No', 'API Group', 'Procedure',"Info","Descriptions")
            );

            // a line across the whole width
            echo str_pad('', $tf->getMaxWidth(), '-') . "\n";

            // colored columns
            $classes = $this->_listClass();
            foreach ($classes as $index => $class) {
                echo $tf->format(
                    array('10%', '20%', '20%','20%','30%'),
                    array(($index+1), $class['apiGroup'], $class['procedure'],$class["info"],$class["description"]),
                    array(Colors::C_RED, Colors::C_YELLOW, Colors::C_YELLOW)
                );
            }
        }elseif ($options->getOpt('tests')){
            $classes = $this->_listClass();
            if (!empty($classes)){
                $map = function ($data) use ($args){
                    return $data;
                };
                $filtered_classes = array_map($map,$classes);
                foreach ($filtered_classes as $index => $filtered_class) {
                    if (empty($filtered_class)){
                        unset($filtered_classes[$index]);
                    }
                }
                $this->info("Found ".count($filtered_classes)." Classes");
                foreach ($filtered_classes as $index => $filtered_class) {
                    $this->info("Running Procedure '".$filtered_class["procedure"]."'");
                    $group = $filtered_class["apiGroup"];
                    $class = $filtered_class["procedure"];
                    $procedure = 'Procedures\\'.$group.''."\\$class";
                    $method = get_class_methods($procedure);
                    $method = array_map(function ($item){
                        if (stripos($item,"test") !== FALSE){
                            return $item;
                        }
                    },$method);
                    $new_method = [];
                    foreach ($method as $index => $item) {
                        if (empty($item)){
                            unset($item);
                        }else{
                            $new_method[] = $item;
                        }
                    }
                    $this->info("Have ".count($new_method)." Steps");
                    $instance = new $procedure();
                    $tf = new TableFormatter($this->colors);
                    $tf->setBorder(' | '); // nice border between colmns

                    echo $tf->format(
                        array('10%', '30%', '20%',"40%"),
                        array('No', 'Name', 'Status', 'Debug')
                    );
                    echo str_pad('', $tf->getMaxWidth(), '-') . "\n";

                    foreach ($new_method as $num => $row_data) {
                        $response = $instance->$row_data($this);
                        echo $tf->format(
                            array('10%', '30%', '20%',"40%"),
                            array(($num+1), $row_data, (($response === TRUE)?"OK":"FAILED"),Core::getDebug()),
                            array(Colors::C_RED, Colors::C_YELLOW, (($response === TRUE)?Colors::C_GREEN:Colors::C_RED))
                        );

                        $env = getenv("APP_DEBUG");
                        if (!$env && !$response){
                            $this->alert("Invalid Result not Allowed on Production Mode");
                            exit();
                        }
                        Core::setDebug("-");
                    }
                }

            }else{
                $this->alert("You Have 0 Procedures, Create Procedure First");
            }
        }elseif ($options->getOpt('test-group')){
            $classes = $this->_listClass();
            if (!empty($classes)){
                $map = function ($data) use ($args){
                    if (!empty($data)){
                        if (in_array($data["apiGroup"],$args)){
                            return $data;
                        }
                    }
                };
                $filtered_classes = array_map($map,$classes);
                foreach ($filtered_classes as $index => $filtered_class) {
                    if (empty($filtered_class)){
                        unset($filtered_classes[$index]);
                    }
                }
                $this->info("Found ".count($filtered_classes)." Classes");
                foreach ($filtered_classes as $index => $filtered_class) {
                    $this->info("Running Procedure '".$filtered_class["procedure"]."'");
                    $group = $filtered_class["apiGroup"];
                    $class = $filtered_class["procedure"];
                    $procedure = 'Procedures\\'.$group.''."\\$class";
                    $method = get_class_methods($procedure);
                    $method = array_map(function ($item){
                        if (stripos($item,"test") !== FALSE){
                            return $item;
                        }
                    },$method);
                    $new_method = [];
                    foreach ($method as $index => $item) {
                        if (empty($item)){
                            unset($item);
                        }else{
                            $new_method[] = $item;
                        }
                    }
                    $this->info("Have ".count($new_method)." Steps");
                    $instance = new $procedure();
                    $tf = new TableFormatter($this->colors);
                    $tf->setBorder(' | '); // nice border between colmns

                    echo $tf->format(
                        array('10%', '30%', '20%',"40%"),
                        array('No', 'Name', 'Status', 'Debug')
                    );
                    echo str_pad('', $tf->getMaxWidth(), '-') . "\n";

                    foreach ($new_method as $num => $row_data) {
                        $response = $instance->$row_data($this);
                        echo $tf->format(
                            array('10%', '30%', '20%',"40%"),
                            array(($num+1), $row_data, (($response === TRUE)?"OK":"FAILED"),Core::getDebug()),
                            array(Colors::C_RED, Colors::C_YELLOW, (($response === TRUE)?Colors::C_GREEN:Colors::C_RED))
                        );
                        Core::setDebug("-");
                    }
                }

            }else{
                $this->alert("You Have 0 Procedures, Create Procedure First");
            }
        }elseif($options->getOpt('test')){

            $classes = $this->_listClass();
            if (!empty($classes)){
                $map = function ($data) use ($args){
                    if (!empty($data)){
                        if (in_array($data["procedure"],$args)){
                            return $data;
                        }
                    }
                };
                $filtered_classes = array_map($map,$classes);
                foreach ($filtered_classes as $index => $filtered_class) {
                    if (empty($filtered_class)){
                        unset($filtered_classes[$index]);
                    }
                }
                $this->info("Found ".count($filtered_classes)." Classes");
                foreach ($filtered_classes as $index => $filtered_class) {
                    $this->info("Running Procedure '".$filtered_class["procedure"]."'");
                    $group = $filtered_class["apiGroup"];
                    $class = $filtered_class["procedure"];
                    $procedure = 'Procedures\\'.$group.''."\\$class";
                    $method = get_class_methods($procedure);
                    $method = array_map(function ($item){
                        if (stripos($item,"test") !== FALSE){
                            return $item;
                        }
                    },$method);
                    $new_method = [];
                    foreach ($method as $index => $item) {
                        if (empty($item)){
                            unset($item);
                        }else{
                            $new_method[] = $item;
                        }
                    }
                    $this->info("Have ".count($new_method)." Steps");
                    $instance = new $procedure();
                    $tf = new TableFormatter($this->colors);
                    $tf->setBorder(' | '); // nice border between colmns

                    echo $tf->format(
                        array('10%', '30%', '20%',"40%"),
                        array('No', 'Name', 'Status', 'Debug')
                    );
                    echo str_pad('', $tf->getMaxWidth(), '-') . "\n";

                    foreach ($new_method as $num => $row_data) {
                        $response = $instance->$row_data($this);
                        echo $tf->format(
                            array('10%', '30%', '20%',"40%"),
                            array(($num+1), $row_data, (($response === TRUE)?"OK":"FAILED"),Core::getDebug()),
                            array(Colors::C_RED, Colors::C_YELLOW, (($response === TRUE)?Colors::C_GREEN:Colors::C_RED))
                        );
                        Core::setDebug("-");
                    }
                }

            }else{
                $this->alert("You Have 0 Procedures, Create Procedure First");
            }
        } else {
            echo $options->help();
        }
    }
}