<?php
PMVC\Load::plug();
PMVC\addPlugInFolders(['../']);
class HttpTest extends PHPUnit_Framework_TestCase
{
    private $_plug = 'http';

    function setup()
    {
        \PMVC\unplug($this->_plug);
    }

    function testPlugin()
    {
        ob_start();
        print_r(PMVC\plug($this->_plug));
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains($this->_plug,$output);
    }

   /**
    * @runInSeparateProcess
    * @preserveGlobalState disabled
    */
    function testGo()
    {
        ob_start();
        $p = \PMVC\plug($this->_plug);
        $p->go('http');
        $output = ob_get_contents();
        ob_end_clean();
        $expected = '<meta http-equiv="refresh" content="0; url=http"><script>location.replace(http)</script>';
        $this->assertEquals($expected,$output);
    }

}
