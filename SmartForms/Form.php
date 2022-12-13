<?php
namespace SmartForms;

use SmartForms\ViewForm;

class Form
{
    private int $id;

    private string $name;

    private ?self $parentForm;

    private string $view;

    private ?string $title;

    private int $sectionCount;

    private bool $changer;

    private bool $deleter;

    /**
     * @var Field[]
     */
    private array $fields;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Form|null
     */
    public function getParentForm(): ?Form
    {
        return $this->parentForm;
    }

    /**
     * @param Form|null $parentForm
     */
    public function setParentForm(?Form $parentForm): void
    {
        $this->parentForm = $parentForm;
    }

    /**
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * @param string $view
     */
    public function setView(string $view): void
    {
        $this->view = $view;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getSectionCount(): int
    {
        return $this->sectionCount;
    }

    /**
     * @param int $sectionCount
     */
    public function setSectionCount(int $sectionCount): void
    {
        $this->sectionCount = $sectionCount;
    }

    /**
     * @return bool
     */
    public function isChanger(): bool
    {
        return $this->changer;
    }

    /**
     * @param bool $changer
     */
    public function setChanger(bool $changer): void
    {
        $this->changer = $changer;
    }

    /**
     * @return bool
     */
    public function isDeleter(): bool
    {
        return $this->deleter;
    }

    /**
     * @param bool $deleter
     */
    public function setDeleter(bool $deleter): void
    {
        $this->deleter = $deleter;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param Field[] $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }


}
