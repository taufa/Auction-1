<?php

namespace auction\models;

use auction\models\core\ActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%categories}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $create_date
 * @property string $update_date
 * @property integer $is_active
 *
 * @property AuctionPreference[] $auctionPreferences
 * @property DealerCompanyPreferences[] $dealerCompanyPreferences
 * @property DealerPreference[] $dealerPreferences
 * @property LotPreference[] $lotPreferences
 * @property Products[] $products
 */
class Categories extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%categories}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'is_active'], 'required'],
            [['description'], 'safe'],
            [['is_active'], 'integer'],
            [['name'], 'string', 'max' => 255, 'min' => 3],
            ['name','unique' ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'create_date' => 'Create Date',
            'update_date' => 'Update Date',
            'is_active' => 'Is Active',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuctionPreferences()
    {
        return $this->hasMany(AuctionPreference::className(), ['category' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealerCompanyPreferences()
    {
        return $this->hasMany(DealerCompanyPreferences::className(), ['category' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealerPreferences()
    {
        return $this->hasMany(DealerPreference::className(), ['category' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLotPreferences()
    {
        return $this->hasMany(LotPreference::className(), ['category' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Products::className(), ['category' => 'id']);
    }
}
