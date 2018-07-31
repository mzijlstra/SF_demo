<?php

/*
 * Michael Zijlstra 03 May 2017
 * 
 * TODO: create proper documentation for this class
 */

class AnnotationReader {

    private $inc_path = array();
    private $security = array();
    private $routing = array();
    private $get_ctrl = array();
    private $post_ctrl = array();
    private $repositories = array();
    private $services = array();
    private $controllers = array();
    public $context = "";

    /**
     * Constructor, initiallizes internal sec array based on global
     * 
     * @global array $SEC_ROLES
     */
    public function AnnotationReader() {
        global $SEC_ROLES;
        $this->security['GET'] = array();
        $this->security['POST'] = array();

        foreach (array_keys($SEC_ROLES) as $k) {
            $this->security['GET'][$k] = array();
            $this->security['POST'][$k] = array();
        }

        $this->routing['GET'] = array();
        $this->routing['POST'] = array();
    }

    /**
     * Helper function to extract annotation attributes (key / value pairs)
     * 
     * @param string $annotation
     * @param string $text
     * @return array<string, string>
     * @throws Exception
     */
    private function annotation_attributes(string $annotation, string $text) {
        $matches = array();
        preg_match("#" . $annotation . "\((.*)\)#", $text, $matches);
        $content = $matches[1];
        // if it's just a single quoted value
        if (preg_match("#^['\"].*['\"]$#", $content)) {
            $content = "value=" . $content;
        }
        $result = array();
        if (!$attrs = preg_split("#,\s*#", $content)) {
            $attrs = array($content);
        }
        foreach ($attrs as $attr) {
            if (!preg_match("#(\w+)\s*=\s*['\"](.*?)['\"]#", $attr, $matches)) {
                throw new Exception("Malformed annotation attribute in "
                . $annotation . " found in " . $text);
            }
            $key = $matches[1];
            $value = $matches[2];
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Checks the properties of a class for @Inject annotations
     * 
     * @param ReflectionClass $reflect_class
     * @return array<string, string>
     */
    private function to_inject(ReflectionClass $reflect_class) {
        $result = array();
        foreach ($reflect_class->getProperties() as $prop) {
            $com = $prop->getDocComment();
            if (preg_match("#@Inject#", $com)) {
                $attrs = $this->annotation_attributes("@Inject", $com);
                $result[$prop->getName()] = $attrs["value"];
            }
        }
        return $result;
    }

    /**
     * Retrieves the value of the specified annotation (which should have only 
     * a single value, not multiple key / value pairs). For all the methods of
     * the given reflection class
     * 
     * @param ReflectionClass $reflect_class
     * @param string $annotation
     * @return array<string, string> method name with value for the annotation
     */
    private function methods_annotation_val(ReflectionClass $reflect_class, string $annotation) {
        $methods = array();
        foreach ($reflect_class->getMethods() as $m) {
            $com = $m->getDocComment();
            $value = "";
            if (preg_match("#$annotation#", $com)) {
                $attrs = $this->annotation_attributes($annotation, $com);
                $value = $attrs['value'];
            }
            $methods[$m->name] = $value;
        }
        return $methods;
    }

    /**
     * Validate the content of @GET and @POST annotations
     * 
     * @param array<string, string> $attrs
     * @param string $com comment
     * @throws Exception
     */
    private function validate_request_annotation(array &$attrs, string $com) {
        if (!isset($attrs['uri']) && !isset($attrs['value'])) {
            throw new Exception("@GET or @POST missing uri attribute in: $com");
        }
        if (!isset($attrs['uri']) && isset($attrs['value'])) {
            $attrs['uri'] = $attrs['value'];
        }
        if (!isset($attrs['sec'])) {
            $attrs['sec'] = "none";
        }
        if (!isset($this->security['GET'][$attrs["sec"]])) {
            throw new Exception("Bad sec value in @GET or @POST found in: $com");
        }
    }

    /**
     * Processes @GET and @POST annotations and creates request mappings used
     * by the routing process and url security mappings used by web security
     * 
     * @param ReflectionClass $reflect_class
     * @param string $req GET or POST
     */
    private function map_requests(ReflectionClass $reflect_class, string $req) {
        foreach ($reflect_class->getMethods() as $m) {
            $com = $m->getDocComment();
            $match = array();

            preg_match_all("#@{$req}\(.*\)#", $com, $match);
            foreach ($match[0] as $a) {
                $attrs = $this->annotation_attributes("@{$req}", $a);
                $this->validate_request_annotation($attrs, $a);
                $method_loc = $reflect_class->getName() . "@" . $m->getName();
                $this->routing[$req][$attrs["uri"]] = $method_loc;
                if ($attrs['uri'][0] === "^") {
                    $attrs['uri'] = substr($attrs['uri'], 1);
                }
                $this->security[$req][$attrs["sec"]][] = $attrs["uri"];
            }
        }
    }

    /**
     * Checks if a class has an @Repository annotation, if it does adds it to
     * the array of repositories
     * 
     * @param string $class
     */
    private function check_repository(string $class) {
        $r = new ReflectionClass($class);
        $doc = $r->getDocComment();
        $class_name = $r->getName();
        if (preg_match("#@Repository#", $doc)) {
            $to_inject = $this->to_inject($r);
            $this->repositories[$class_name] = $to_inject;
        }
    }

    /**
     * Checks if a class has an @Service annotation, if it does it adds it to 
     * the array of services
     * 
     * @param string $class
     */
    private function check_service(string $class) {
        $r = new ReflectionClass($class);
        $doc = $r->getDocComment();
        $class_name = $r->getName();
        if (preg_match("#@Service#", $doc)) {
            $info = array();
            $info['props'] = $this->to_inject($r);
            $info['auth'] = $this->methods_annotation_val($r, "@Security");
            $info['tx_and_log'] = $this->methods_annotation_val($r, "@Service");
            $this->services[$class_name] = $info;
        }
    }

    /**
     * Checks if classes have a @Controller or a @WebService annotation, and
     * the processes them as needed
     * 
     * @param string $class
     */
    private function check_controller(string $class) {
        $r = new ReflectionClass($class);
        $doc = $r->getDocComment();
        $class_name = $r->getName();
        if (preg_match("#@Controller#", $doc) ||
                preg_match("#@WebService#", $doc)) {
            $to_inject = $this->to_inject($r);
            $this->controllers[$class_name] = $to_inject;
            $this->map_requests($r, "GET");
            $this->map_requests($r, "POST");
        }
    }

    /**
     * Scan for PHP classes in a directory, calling the passed function on them
     * 
     * @param string $directory
     * @param string $function
     */
    private function scan_classes(string $directory, string $function) {
        set_include_path(get_include_path() . PATH_SEPARATOR . "$directory");
        $this->inc_path[] = $directory;
        $files = scandir($directory);
        foreach ($files as $file) {
            $mats = array();
            // skip hidden files, directories, files that are not .class.php
            if ($file{0} === "." ||
                    !preg_match("#(.*)\.class\.php#i", $file, $mats)) {
                continue;
            }
            if (is_dir($file)) {
                $this->scan_classes("$directory/$file", $function);
                continue;
            }
            $this->{$function}($mats[1]);
        }
    }

    /**
     * Generate the code (text) to output the security array
     */
    private function generate_security_array() {
        $this->context .= "\$security = array(\n";
        $this->context .= "\t'GET' => array(\n";
        foreach ($this->security['GET'] as $lvl => $items) {
            foreach ($items as $item) {
                $this->context .= "\t\t'|$item|' => '$lvl',\n";
            }
        }
        $this->context .= "\t),\n";
        $this->context .= "\t'POST' => array(\n";
        foreach ($this->security['POST'] as $lvl => $items) {
            foreach ($items as $item) {
                $this->context .= "\t\t'|$item|' => '$lvl',\n";
            }
        }
        $this->context .= "\t)\n";
        $this->context .= ");\n";
    }

    /**
     * Generate the code (text) to output the routing array:
     */
    private function generate_routing_arrays() {
        $this->context .= "\$routing = array(\n";
        $this->context .= "\t'GET' => array(\n";
        foreach ($this->routing['GET'] as $uri => $method_loc) {
            $this->context .= "\t\t'|$uri|' => '$method_loc',\n";
        }
        $this->context .= "\t),\n";
        $this->context .= "\t'POST' => array(\n";
        foreach ($this->routing['POST'] as $uri => $method_loc) {
            $this->context .= "\t\t'|$uri|' => '$method_loc',\n";
        }
        $this->context .= "\t)\n";
        $this->context .= ");\n\n";
    }

    /**
     * Generate the code (text) for a service method
     * @param ReflectionMethod $m
     * @param array<string, array> $info 
     * @return void
     */
    private function generate_service_proxy_method(ReflectionMethod $m, $info) {
        if (!$m->isPublic()) {
            return;
        }
        $params = array();
        $pnames = array();
        foreach ($m->getParameters() as $p) {
            if ($p->isOptional()) {
                $default = $p->getDefaultValue() ? $p->getDefaultValue() : "false";
                $params[] = "\${$p->name}={$default}";
            } else {
                $params[] = "\${$p->name}";
            }
            $pnames[] = "\${$p->name}";
        }
        $pr = join(", ", $params);
        $pn = join(", ", $pnames);

        $call = "{$m->class}.{$m->name}";
        $this->context .= "    public function {$m->name}({$pr}){\n";
        $this->context .= "        global \$DB;\n";
        if (isset($info['auth'][$m->name])) {
            $role = $info['auth'][$m->name];
            $this->context .= "        if (!isAuthorized('$role')) {\n";
            $this->context .= "            throw new AuthorizationException('$call');\n";
            $this->context .= "        }\n";
        }
        $tx = false;
        if (isset($info['tx_and_log'][$m->name]) &&
                $info['tx_and_log'][$m->name] !== "notx") {
            $tx = true;
            $this->context .= "        try {\n";
            $this->context .= "            \$DB->beginTransaction();\n";
            if ($info['tx_and_log'][$m->name] !== "nolog") {
                $this->context .= "            auditLog('$call');\n";
            }
        }
        $this->context .= "            \$result = \$this->actual->{$m->name}({$pn});\n";
        if ($tx) {
            $this->context .= "            \$DB->commit();\n";
            $this->context .= "        } catch (Exception \$e) {\n ";
            $this->context .= "            \$DB->rollBack();\n";
            $this->context .= "            throw \$e;\n";
            $this->context .= "        }\n";
        }
        $this->context .= "        return \$result;";
        $this->context .= "    }\n";
    }

    /**
     * Generate code (text) for a proxy class for each of the service classes
     */
    private function generate_service_proxies() {
        foreach ($this->services as $service => $info) {
            $this->context .= "class Proxy{$service} {\n";
            $this->context .= "    public \$actual;\n";
            $r = new ReflectionClass($service);
            foreach ($r->getMethods() as $m) {
                $this->generate_service_proxy_method($m, $info);
            }
            $this->context .= "}\n";
        }
    }

    /**
     * Generate code for the retrievable classes to the context class
     * 
     * @param array $classes
     */
    private function add_classes_to_context(array $classes) {
        foreach ($classes as $class => $injects) {
            $this->context .= <<< IF_START
        if (\$id === "$class") {
            \$this->objects["$class"] = new $class();

IF_START;
            foreach ($injects as $prop => $id) {
                $this->context .=
                        "            \$this->objects[\"$class\"]->$prop = "
                        . "\$this->get(\"$id\");\n";
            }
            $this->context .= "        }\n"; // close if statement
        }
    }

    /**
     * Generate code for the retrievable service classes to the context class
     */
    private function add_services_to_context() {
        foreach ($this->services as $class => $info) {
            $injects = $info['props'];
            $this->context .= <<< IF_START
        if (\$id === "$class") {
            \$proxy = new Proxy{$class}();
            \$this->objects["$class"] = \$proxy;
            \$actual = new $class();

IF_START;
            foreach ($injects as $prop => $id) {
                $this->context .=
                        "            \$actual->$prop = "
                        . "\$this->get(\"$id\");\n";
            }
            $this->context .= "           \$proxy->actual = \$actual;";
            $this->context .= "        }\n"; // close if statement
        }
    }

    /**
     * Scans the standard directories, reading .php files for annotations
     * 
     * @return AnnotationReader self for call chaining
     */
    public function scan() {
        $this->scan_classes("./model", "check_repository");
        $this->scan_classes("./service", "check_service");
        $this->scan_classes("./control", "check_controller");
        return $this;
    }

    /**
     * Creates the context in memory based on the results of scan()
     * 
     * @return AnnotationReader self for call chaining
     */
    public function create_context() {
        $this->generate_security_array();
        $this->generate_routing_arrays();
        $this->generate_service_proxies();
        $path = join(PATH_SEPARATOR, $this->inc_path);

        $this->context .= <<< HEADER
set_include_path(get_include_path() . PATH_SEPARATOR . "$path");
class Context {
    private \$objects = array();
    
    public function Context() {
        global \$DB;
        \$this->objects["DB"] = \$DB;
    }

    public function get(\$id) {
        if (isset(\$this->objects[\$id])) {
            return \$this->objects[\$id];
        }

HEADER;

        $this->add_classes_to_context($this->repositories);
        $this->add_classes_to_context($this->controllers);
        $this->add_services_to_context();

        $this->context .= <<< FOOTER
        return \$this->objects[\$id];
    } // close get method
} // close Context class
FOOTER;

        return $this;
    }

    /**
     * Writes the context (as found by scan) to a file
     * 
     * @param string $filename
     * @return AnnotationReader self for call chaining
     */
    public function write(string $filename) {
        $data = "<?php\n" . $this->context;
        file_put_contents($filename, $data);
        return $this;
    }

}
