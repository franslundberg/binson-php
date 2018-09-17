<?php
use PHPUnit\Framework\TestCase;

//require_once(__DIR__ . '/../../out/binson.php');
require_once(SRC_DIR . 'binson.php');

class ExtBinsonParserTest extends TestCase
{

    public function testEmptyObject()
    {   
        $buf = "\x40\x41";
        $parser = new BinsonParser($buf);

        $this->assertSame(0, $parser->getDepth());

        $parser->goIntoObject();

        $this->assertSame(1, $parser->getDepth());
        $this->assertSame(binson::BINSON_TYPE_OBJECT, $parser->getType());

        $parser->leaveObject();

        $this->assertSame(0, $parser->getDepth());
        $this->assertSame(binson::BINSON_TYPE_NONE, $parser->getType());
    }    

    public function testBoolean()
    {   
        $buf = "\x42\x45\x43";
        $parser = new BinsonParser($buf, binson::BINSON_TYPE_ARRAY);

        $parser->goIntoArray();

        $this->assertSame(1, $parser->getDepth());
        $this->assertSame(binson::BINSON_TYPE_ARRAY, $parser->getType());

        $parser->next();

        $this->assertSame(1, $parser->getDepth());
        $this->assertSame(binson::BINSON_TYPE_BOOLEAN, $parser->getType());

        $this->assertSame(false, $parser->getBoolean());
    }    

    public function testInteger()
    {   
        $buf = "\x42\x10\x7b\x43";
        $parser = new BinsonParser($buf, binson::BINSON_TYPE_ARRAY);

        $parser->goIntoArray();

        $this->assertSame(1, $parser->getDepth());
        $this->assertSame(binson::BINSON_TYPE_ARRAY, $parser->getType());

        $parser->next();

        $this->assertSame(1, $parser->getDepth());
        $this->assertSame(binson::BINSON_TYPE_INTEGER, $parser->getType());

        $this->assertSame(123, $parser->getInteger());
    }

    public function testDouble()
    {   
        $buf = "\x42\x46\xae\x47\xe1\x7a\x14\xae\xf3\x3f\x43";
        $parser = new BinsonParser($buf, binson::BINSON_TYPE_ARRAY);

        $parser->goIntoArray();

        $this->assertSame(1, $parser->getDepth());
        $this->assertSame(binson::BINSON_TYPE_ARRAY, $parser->getType());

        $parser->next();

        $this->assertSame(1, $parser->getDepth());
        $this->assertSame(binson::BINSON_TYPE_DOUBLE, $parser->getType());

        $this->assertSame(1.23, $parser->getDouble());
    }

    public function testName()
    {   
        // { "a":123, "bcd":"Hello world!" }
        $buf = "\x40\x14\x01\x61\x10\x7b\x14\x03\x62\x63\x64\x14\x0c\x48\x65\x6c\x6c\x6f\x20\x77\x6f\x72\x6c\x64\x21\x41";
        $parser = new BinsonParser($buf);

        $parser->goIntoObject();
        $parser->next();
        $this->assertSame("a", $parser->getName());

        $parser->next();
        $this->assertSame("bcd", $parser->getName());
    }

    public function testString()
    {   
        // ["bcd"]
        $buf = "\x42\x14\x03\x62\x63\x64\x43";
        $parser = new BinsonParser($buf, binson::BINSON_TYPE_ARRAY);

        $parser->goIntoArray()->next();
        $this->assertSame("bcd", $parser->getString());
    }

    public function testBytes()
    {   
        // ['\x01\x00\x02\x03\x00']
        $buf = "\x42\x18\x05\x01\x00\x02\x03\x00\x43";
        $parser = new BinsonParser($buf, binson::BINSON_TYPE_ARRAY);

        $parser->goIntoArray()->next();
        $this->assertSame(binson::BINSON_TYPE_BYTES, $parser->getType());
        
        $this->markTestSkipped('do not work for now');
        $bytes_str = $parser->getBytes();

        $this->assertSame(5, strlen($bytes_str));
        $this->assertSame("\x01\x00\x02\x03\x00", $bytes_str);
    }
   

    public function testField()
    {   
        // { "a":123, "bcd":"Hello world!" }
        $buf = "\x40\x14\x01\x61\x10\x7b\x14\x03\x62\x63\x64\x14\x0c\x48\x65\x6c\x6c\x6f\x20\x77\x6f\x72\x6c\x64\x21\x41";
        $parser = new BinsonParser($buf);

        $parser->goIntoObject();
        $parser->field("a");
        $this->assertSame(123, $parser->getInteger());

        $parser->field("bcd");
        $this->assertSame("Hello world!", $parser->getString());
    }

    public function testNext()
    {   
        // { "a":123, "bcd":"Hello world!" }
        $buf = "\x40\x14\x01\x61\x10\x7b\x14\x03\x62\x63\x64\x14\x0c\x48\x65\x6c\x6c\x6f\x20\x77\x6f\x72\x6c\x64\x21\x41";
        $parser = new BinsonParser($buf);

        $parser->goIntoObject();
        $parser->next();
        $this->assertSame(123, $parser->getInteger());


        $parser->next();
        $this->assertSame("Hello world!", $parser->getString());
    }


    public function test__toBytes()
    {   
        $this->markTestSkipped('do not work for now');
        
        // {"a":[true,123,"b",5],"b":false,"c":7} => [true,123,"b",5]
        $b1 = "\x40\x14\x01\x61\x42\x44\x10\x7b\x14\x01\x62\x10\x05\x43\x14\x01\x62\x45\x14\x01\x63\x10\x07\x41";
        $parser = new BinsonParser($b1);

        $parser->goIntoObject()->field("a");
        $this->assertSame("\x42\x44\x10\x7b\x14\x01\x62\x10\x05\x43", $parser->toBytes());

    }

 
    public function test__toString()
    {   
        $buf = "\x40\x41";
        $parser = new BinsonParser($buf);

        $this->assertSame("{}", $parser->toString(false));
    }

}

?>