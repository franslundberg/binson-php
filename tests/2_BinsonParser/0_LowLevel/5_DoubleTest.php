<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsNan;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group parser
*/
class DoubleParserTest extends TestCase
{
    private function processDouble(float $arg, bool $is_nan = false)
    {
        $buf = "";
        $writer = new BinsonWriter($buf);

        $writer->arrayBegin()->putDouble($arg)->arrayEnd();

        $parser = new BinsonParser($buf);
        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_DOUBLE, $parser->getType());

        // true by definition: NAN != NAN
        if ($is_nan)
            $this->assertNotSame($arg, $parser->getValue(binson::TYPE_DOUBLE));
        else
            $this->assertSame($arg, $parser->getValue(binson::TYPE_DOUBLE));       
    }
    
    public function testDoublePlusZero()
    { $this->processDouble(+0.0); }

    public function testDoubleMinusZero()
    { $this->processDouble(-0.0); }

    public function testDoublePositiveExponent()
    { $this->processDouble(+3.1415e+10); }

    public function testDoubleNegativeExponent()
    { $this->processDouble(-3.1415e-10); }

    public function testDouble_NAN_()
    { $this->processDouble(NAN, true); }

    public function testDoublePlusInfinity()
    { $this->processDouble(+INF); }

    public function testDoubleMinusInfinity()
    { $this->processDouble(-INF); }
}

?>