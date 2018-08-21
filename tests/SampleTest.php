<?php
use PHPUnit\Framework\TestCase;
class SampleTest extends TestCase{
    // public function testTrueAssertsToTrue(){
    //     $this->assertFalse(false);
    // }
    public function testAddition(){
        include('func.php'); // must include if tests are for non OOP code
        $result = my_addition(1,1);
        $this->assertEquals(2, $result);
    }
}
?>