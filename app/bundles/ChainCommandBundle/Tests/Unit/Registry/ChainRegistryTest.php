<?php

namespace ChainCommandBundle\Tests\Registry;

use ChainCommandBundle\Registry\ChainRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Class ChainRegistryTest
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 */
class ChainRegistryTest extends TestCase
{
    private const MASTER_COMMAND = 'test:master';
    private const MEMBER_COMMAND = 'test:member';

    /**
     * @var ChainRegistry
     */
    private ChainRegistry $registry;

    /**
     * Method setUp
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->registry = new ChainRegistry();
    }

    /**
     * Method testRegisterChainMember covered: add members and set sort order structure
     *
     * @return void
     */
    public function testRegisterChainMember(): void
    {
        $this->registry->registerChainMember(self::MASTER_COMMAND, self::MEMBER_COMMAND, 10);
        $this->registry->registerChainMember(self::MASTER_COMMAND, 'test:test1', 20);
        $this->registry->registerChainMember(self::MASTER_COMMAND, 'test:test2');

        $chainMembers = $this->registry->getChainMembers(self::MASTER_COMMAND);
        $expectedStructure = [
            10 => self::MEMBER_COMMAND,
            11 => 'test:test2',
            20 => 'test:test1',
        ];

        $this->assertEquals($expectedStructure, $chainMembers);
    }

    /**
     * Method testGetChainMembersForNonExistentMaster
     *
     * @return void
     */
    public function testGetChainMembersForNonExistentMaster(): void
    {
        $chainMembers = $this->registry->getChainMembers('non:master');
        $this->assertIsArray($chainMembers);
        $this->assertEmpty($chainMembers);
    }

    /**
     * Method testIsChainMasterAndMember
     *
     * @return void
     */
    public function testIsChainMasterAndMember(): void
    {
        $this->registry->registerChainMember(self::MASTER_COMMAND, self::MEMBER_COMMAND);

        $this->assertTrue($this->registry->isChainMaster(self::MASTER_COMMAND));
        $this->assertTrue($this->registry->isChainMember(self::MEMBER_COMMAND));

        $this->assertFalse($this->registry->isChainMaster(self::MEMBER_COMMAND));
        $this->assertFalse($this->registry->isChainMember(self::MASTER_COMMAND));
        $this->assertFalse($this->registry->isChainMaster('non:master'));
        $this->assertFalse($this->registry->isChainMember('non:master'));
    }

    /**
     * Method testGetMasterForNonExistentMember
     *
     * @return void
     */
    public function testGetMasterForNonExistentMember(): void
    {
        $master = $this->registry->getMasterForMember('non:master');
        $this->assertNull($master);
    }
}