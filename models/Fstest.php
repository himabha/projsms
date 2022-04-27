<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fstest".
 *
 * @property string $Country
 * @property string $Number_Range
 * @property string $Test_Number
 * @property double $Rate
 */
class Fstest extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fstest';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Country', 'Number_Range', 'Test_Number', 'Rate'], 'required'],
            [['Rate'], 'number'],
            [['Country', 'Number_Range'], 'string', 'max' => 255],
            [['Test_Number'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Country' => 'Country',
            'Test_Number' => 'Test  Number',
            'Rate' => 'Rate [USD]',
            'Number_Range' => 'Payment Terms'
        ];
    }
}
