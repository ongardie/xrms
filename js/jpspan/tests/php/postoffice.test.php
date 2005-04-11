<?php
/**
* @version $Id: postoffice.test.php,v 1.1 2005/04/11 19:50:50 gpowers Exp $
* @package JPSpan
* @subpackage Tests
*/

/**
* Includes
*/
require_once('../config.php');
require_once JPSPAN . 'Server/PostOffice.php';

/**
* @package JPSpan
* @subpackage Tests
*/
class JPSpan_TestHandler_PostOffice {
    function test() {
        return 'test';
    }
    
    function hello($name) {
        return 'Hello '.$name;
    }
    
    function add($x,$y) {
        return $x + $y;
    }
    
    function getlist($array) {
        $array[] = 'test';
        return $array;
    }
}

/**
* @package JPSpan
* @subpackage Tests
*/
class TestOfJPSpan_Server_PostOffice extends UnitTestCase {

    var $server_name;
    var $script_name;
    var $request_uri;
    var $post;
    var $rawpost;

    function TestOfJPSpan_Server_PostOffice() {
        $this->UnitTestCase('TestOfJPSpan_Server_PostOffice');
        $this->server_name = $_SERVER['SERVER_NAME'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
        $this->request_uri = $_SERVER['REQUEST_URI'];
        $this->post = $_POST;
        global $HTTP_RAW_POST_DATA;
        $this->rawpost = $HTTP_RAW_POST_DATA;
    }
    
    function tearDown() {
        $_SERVER['SERVER_NAME'] = $this->server_name;
        $_SERVER['SCRIPT_NAME'] = $this->script_name;
        $_SERVER['REQUEST_URI'] = $this->request_uri;
        $_POST = $this->post;
        global $HTTP_RAW_POST_DATA;
        $HTTP_RAW_POST_DATA = $this->rawpost;
    }

    function testInvalidCallSyntax1() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php?foo=bar';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $S->serve(FALSE);
        $this->assertErrorPattern('/Invalid call syntax/');
    }
    
    function testInvalidCallSyntax2() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/foo';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $S->serve(FALSE);
        $this->assertErrorPattern('/Invalid call syntax/');
    }
    
    function testInvalidHandlerName1() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/2/bar';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $S->serve(FALSE);
        $this->assertErrorPattern('/Invalid handler name/');
    }
    
    function testInvalidHandlerName2() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/FOO/bar';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $S->serve(FALSE);
        $this->assertErrorPattern('/Invalid handler name/');
    }
    
    function testInvalidHandlerMethod1() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/foo/2';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $S->serve(FALSE);
        $this->assertErrorPattern('/Invalid handler method/');
    }
    
    function testInvalidHandlerMethod2() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/foo/BAR';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $S->serve(FALSE);
        $this->assertErrorPattern('/Invalid handler method/');
    }
    
    function testUnknownHandler() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/foo/bar';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $S->serve(FALSE);
        $this->assertErrorPattern('/Unknown handler/');
    }
    
    function testUnknownHandlerMethod() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/jpspan_testhandler_postoffice/bar';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $H = & new JPSpan_TestHandler_PostOffice();
        $S->addHandler($H);
        $S->serve(FALSE);
        $this->assertErrorPattern('/Unknown handler method/');
    }
    
    function testServe() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/jpspan_testhandler_postoffice/test';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $H = & new JPSpan_TestHandler_PostOffice();
        $S->addHandler($H);
        ob_start();
        $this->assertTrue($S->serve(FALSE));
        $content = ob_get_contents();
        ob_end_clean();
        $expected = 'new Function("var t1 = \\\'test\\\';return t1;");';
        $this->assertEqual($expected,$content);
    }
    
    function testServeParam() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/jpspan_testhandler_postoffice/hello';
        $_POST['name'] = 'Joe';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $H = & new JPSpan_TestHandler_PostOffice();
        $S->addHandler($H);
        ob_start();
        $this->assertTrue($S->serve(FALSE));
        $content = ob_get_contents();
        ob_end_clean();
        $expected = 'new Function("var t1 = \\\'Hello Joe\\\';return t1;");';
        $this->assertEqual($expected,$content);
    }
    
    function testServeParams() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/jpspan_testhandler_postoffice/add';
        $_POST['x'] = '3';
        $_POST['y'] = '4';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $H = & new JPSpan_TestHandler_PostOffice();
        $S->addHandler($H);
        ob_start();
        $this->assertTrue($S->serve(FALSE));
        $content = ob_get_contents();
        ob_end_clean();
        $expected = 'new Function("var t1 = parseInt(\\\'7\\\');return t1;");';
        $this->assertEqual($expected,$content);
    }
    
    function testGetList() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/jpspan_testhandler_postoffice/getlist';
        $_POST['whatever'] = array('a','b','c');
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'php';
        $H = & new JPSpan_TestHandler_PostOffice();
        $S->addHandler($H);
        ob_start();
        $this->assertTrue($S->serve(FALSE));
        $content = ob_get_contents();
        ob_end_clean();
        $expected = 'new Function("var t1 = new Array();var t2 = \\\'a\\\';t1[0] = t2;var t3 = \\\'b\\\';t1[1] = t3;var t4 = \\\'c\\\';t1[2] = t4;var t5 = \\\'test\\\';t1[3] = t5;t1.toString = function() { var str = \\\'[\\\';var sep = \\\'\\\';for (var prop in this) { if (prop == \\\'toString\\\') { continue; }str+=sep+prop+\\\': \\\'+this[prop];sep = \\\', \\\';} return str+\\\']\\\';};return t1;");';
        $this->assertEqual($expected,$content);
    }
    
    function testRequestXml() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php/jpspan_testhandler_postoffice/hello';
        global $HTTP_RAW_POST_DATA;
        $HTTP_RAW_POST_DATA = '<?xml version="1.0" encoding="UTF-8"?><r><a><e k="0"><s>Joe</s></e></a></r>';
        $S = & new JPSpan_Server_PostOffice();
        $S->RequestEncoding = 'xml';
        $H = & new JPSpan_TestHandler_PostOffice();
        $S->addHandler($H);
        ob_start();
        $this->assertTrue($S->serve(FALSE));
        $content = ob_get_contents();
        ob_end_clean();
        $expected = 'new Function("var t1 = \\\'Hello Joe\\\';return t1;");';
        $this->assertEqual($expected,$content);
    }
    
}

/**
* Conditional test runner
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfJPSpan_Server_PostOffice();
    $test->run(new HtmlReporter());
}
?>
