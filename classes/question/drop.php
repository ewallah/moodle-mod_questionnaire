<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the parent class for drop question types.
 *
 * @author Mike Churchward
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questiontypes
 */

namespace mod_questionnaire\question;
defined('MOODLE_INTERNAL') || die();
use \html_writer;

class drop extends base {

    protected function responseclass() {
        return '\\mod_questionnaire\\response\\single';
    }

    public function helpname() {
        return 'dropdown';
    }

    /**
     * Return true if the question has choices.
     */
    public function has_choices() {
        return true;
    }

    /**
     * Override and return a form template if provided. Output of question_survey_display is iterpreted based on this.
     * @return boolean | string
     */
    public function question_template() {
        return 'mod_questionnaire/question_drop';
    }

    /**
     * Return the context tags for the check question template.
     * @param object $data
     * @param string $descendantdata
     * @param boolean $blankquestionnaire
     * @return object The check question context tags.
     *
     */
    protected function question_survey_display($data, $descendantsdata, $blankquestionnaire=false) {
        // Drop.
        $output = '';
        $options = [];

        $choicetags = new \stdClass();
        $choicetags->qelements = [];
        $selected = isset($data->{'q'.$this->id}) ? $data->{'q'.$this->id} : false;
        // To display or hide dependent questions on Preview page.
        if ($descendantsdata) {
            $qdropid = 'q'.$this->id;
            $descendants = implode(',', $descendantsdata['descendants']);
            foreach ($descendantsdata['choices'] as $key => $choice) {
                $choices[$key] = implode(',', $choice);
            }
            $options[] = ['value' => '', 'label' => get_string('choosedots')];
            foreach ($this->choices as $key => $choice) {
                if ($pos = strpos($choice->content, '=')) {
                    $choice->content = substr($choice->content, $pos + 1);
                }
                if (isset($choices[$key])) {
                    $value = $choices[$key];
                } else {
                    $value = $key;
                }
                $option = [];
                $option['value'] = $value;
                $option['label'] = $choice->content;
                if (($selected !== false) && ($value == $selected)) {
                    $option['selected'] = true;
                }
                $options[] = $option;
            }
            $dependdrop = "dependdrop('$qdropid', '$descendants')";
            $choicetags->qelements['choice']['name'] = $qdropid;
            $choicetags->qelements['choice']['id'] = $qdropid;
            $choicetags->qelements['choice']['class'] = 'select custom-select menu'.$qdropid;
            $choicetags->qelements['choice']['onchange'] = $dependdrop;
            $choicetags->qelements['choice']['options'] = $options;
            // End dependents.
        } else {
            $options[] = ['value' => '', 'label' => get_string('choosedots')];
            foreach ($this->choices as $key => $choice) {
                if ($pos = strpos($choice->content, '=')) {
                    $choice->content = substr($choice->content, $pos + 1);
                }
                $option = [];
                $option['value'] = $key;
                $option['label'] = $choice->content;
                if (($selected !== false) && ($key == $selected)) {
                    $option['selected'] = true;
                }
                $options[] = $option;
            }
            $choicetags->qelements['choice']['name'] = 'q'.$this->id;
            $choicetags->qelements['choice']['id'] = $this->type . $this->id;
            $choicetags->qelements['choice']['class'] = 'select custom-select menu q'.$this->id;
            $choicetags->qelements['choice']['options'] = $options;
        }

        return $choicetags;
    }

    protected function response_survey_display($data) {
        static $uniquetag = 0;  // To make sure all radios have unique names.

        $output = '';

        $options = array();
        foreach ($this->choices as $id => $choice) {
            $contents = questionnaire_choice_values($choice->content);
            $options[$id] = format_text($contents->text, FORMAT_HTML);
        }
        $output .= '<div class="response drop">';
        $output .= html_writer::select($options, 'q'.$this->id.$uniquetag++,
            (isset($data->{'q'.$this->id}) ? $data->{'q'.$this->id} : ''));
        if (isset($data->{'q'.$this->id}) ) {
            $output .= ': <span class="selected">'.$options[$data->{'q'.$this->id}].'</span></div>';
        }

        return $output;
    }

    protected function form_length(\MoodleQuickForm $mform, $helpname = '') {
        return base::form_length_hidden($mform);
    }

    protected function form_precise(\MoodleQuickForm $mform, $helpname = '') {
        return base::form_precise_hidden($mform);
    }
}