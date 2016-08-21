<?php

namespace CSVObjects\ImportBundle\Tests\ObjectProcurer;

use CSVObjects\ImportBundle\ObjectProcurer\ObjectProcurer;
use CSVObjects\ImportBundle\Tests\Objects\Contract;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ObjectProcurerTest extends KernelTestCase
{
    public function testConstructor()
    {
        $objectProcurer = new ObjectProcurer(Contract::class);
        $contract       = $objectProcurer->procure(1);

        $this->assertInstanceOf(Contract::class, $contract);
    }

    public function testConstructorArrayCall()
    {
        $objectProcurer = new ObjectProcurer(Contract::class);
        $contract       = $objectProcurer->procure([1]);

        $this->assertInstanceOf(Contract::class, $contract);
    }

    public function testStaticCall()
    {
        $objectProcurer = new ObjectProcurer([Contract::class, 'getContractFromId']);
        $contract       = $objectProcurer->procure(1);

        $this->assertInstanceOf(Contract::class, $contract);
    }

    public function testStaticCallArrayCall()
    {
        $objectProcurer = new ObjectProcurer([Contract::class, 'getContractFromId']);
        $contract       = $objectProcurer->procure([1]);

        $this->assertInstanceOf(Contract::class, $contract);
    }
}
