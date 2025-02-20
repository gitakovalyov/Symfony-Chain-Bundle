<?php

namespace ChainCommandBundle\Registry;

/**
 * Class ChainRegistry
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 */
class ChainRegistry
{
    /**
     * @const string
     */
    public const MEMBER_TAG = 'chain_command.member';

    /**
     * @const string
     */
    public const MEMBER_TAG_MASTER_ATTR = 'master';

    /**
     * @const string
     */
    public const MEMBER_TAG_SORT_ATTR = 'sort';

    /**
     * @const string
     */
    public const MEMBER_TAG_DEFAULT_SORT_ATTR = 100;

    /**
     * @var array
     */
    private array $chainMasterToMembers = [];

    /**
     * @var array
     */
    private array $memberToMaster = [];

    /**
     * Method registerChainMember
     *
     * @param string $masterCommandName
     * @param string $memberCommandName
     * @param int $sort
     * @return void
     */
    public function registerChainMember(string $masterCommandName, string $memberCommandName, int $sort = 10): void
    {
        while (isset($this->chainMasterToMembers[$masterCommandName][$sort])) {
            $sort++;
        }

        $this->chainMasterToMembers[$masterCommandName][$sort] = $memberCommandName;
        $this->memberToMaster[$memberCommandName] = $masterCommandName;
    }

    /**
     * Method getChainMembers
     *
     * @param string $masterCommandName
     * @return array
     */
    public function getChainMembers(string $masterCommandName): array
    {
        if (isset($this->chainMasterToMembers[$masterCommandName])) {
            ksort($this->chainMasterToMembers[$masterCommandName]);
        }
        return $this->chainMasterToMembers[$masterCommandName] ?? [];
    }

    /**
     * Method isChainMaster
     *
     * @param string $commandName
     * @return bool
     */
    public function isChainMaster(string $commandName): bool
    {
        return !empty($this->chainMasterToMembers[$commandName]);
    }

    /**
     * Method isChainMember
     *
     * @param string $commandName
     * @return bool
     */
    public function isChainMember(string $commandName): bool
    {
        return isset($this->memberToMaster[$commandName]);
    }

    /**
     * Method getMasterForMember
     *
     * @param string $memberCommandName
     * @return string|null
     */
    public function getMasterForMember(string $memberCommandName): ?string
    {
        return $this->memberToMaster[$memberCommandName] ?? null;
    }
}