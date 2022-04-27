<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fsaccess".
 *
 * @property string $Called_number
 * @property string $Number_Range
 * @property string $Test_Number
 * @property double $Rate
 */
class Fsaccess extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fsaccess';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['called_number', 'caller_number', 'caller_origination', 'called_destination'], 'required'],
            [['caller_origination', 'called_destination'], 'string', 'max' => 255],
            [['called_number','caller_number'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'called_number' => 'Called Number',
            'caller_number' => 'Caller Number',
            'caller_origination' => 'Caller Origination',
            'called_destination' => 'Called Destination'
        ];
    }
}
