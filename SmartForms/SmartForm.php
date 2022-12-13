<?php

namespace SmartForms;

class SmartForm
{
    private const FORMS_TABLE = 'admin_forms';

    private const FIELDS_TABLE = 'admin_form_fields';

    public function buildHTML(Form $form): string {
        $titleTag = $form->getView() == ViewForm::DIV ? 'h5' : 'summary';

        foreach ($form->getFields() as $field) {

        }

        $html = "<{$form->getView()}>";
        $html .= "<{$titleTag}>{$form->getTitle()}</{$titleTag}>";
        $html .= "<div>";

        $html .= "<div>";

        $html .= "</div>";
        $html .= "</{$form->getView()}>";

        return $html;
    }
}