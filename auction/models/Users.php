<?php

namespace auction\models;

use auction\components\Auction;
use auction\models\core\ActiveRecord;
use auction\models\core\Expression;
use auction\models\core\TimestampBehaviour;
use common\models\User;
use Yii;
use auction\components\helpers\DatabaseHelper;
use auction\components\Events;
use auction\components\EventHandler;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $mobile
 * @property string $password
 * @property string $last_login
 * @property string $last_login_ip
 * @property string $user_role
 * @property integer $is_active
 * @property string $profile_pic
 * @property string $auth_key
 * @property string $created_at
 * @property string $updated_at
 *
 * @property CompanyUsers[] $companyUsers
 * @property Dealers[] $dealers
 * @property ForgotPasswordHistory[] $forgotPasswordHistories
 * @property OptHistory[] $optHistories
 * @property Role $userRole
 */
class Users extends User
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehaviour::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }


    public function init(){
        //User Login Event handler
        $this->on(Events::USER_LOGIN, [EventHandler::className(), 'UserLogin']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'email', 'mobile', 'user_role'], 'required'],
            [['last_login', 'created_at', 'updated_at'], 'safe'],
            [['is_active'], 'integer'],
            [['name', 'email', 'mobile', 'password', 'profile_pic', 'auth_key'], 'string', 'max' => 255],
            [['user_role'], 'string', 'max' => 25],
            ['email' ,'email'],
            [['email' , 'mobile'] , 'unique']
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
            'email' => 'Email',
            'mobile' => 'Mobile Number',
            'password' => 'Password',
            'last_login' => 'Last Login',
            'last_login_ip' => 'Last Login Ip',
            'user_role' => 'User Role',
            'is_active' => 'Status',
            'profile_pic' => 'Profile Pic',
            'auth_key' => 'Auth Key',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyUsers()
    {
        return $this->hasOne(CompanyUsers::className(), ['user' => 'id']);
    }

    //GetCompany
    public function getCompany(){
        return $this->hasOne(Companies::className(),['id' => 'company'])->viaTable(CompanyUsers::tableName(),['user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDealers()
    {
        return $this->hasOne(Dealers::className(), ['user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForgotPasswordHistories()
    {
        return $this->hasMany(ForgotPasswordHistory::className(), ['user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOptHistories()
    {
        return $this->hasMany(OptHistory::className(), ['user' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRole()
    {
        return $this->hasOne(Role::className(), ['id' => 'user_role']);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Finds user by username/mobile
     *
     * @param string $username/mobile
     * @return static|null
     */
    public static function findByUsername($username,$isActive = true)
    {
        $query =  static::find()->where([
            'or', 'users.email=:email', 'users.mobile=:mobile'
        ])->addParams([
            ':email' => $username,
            ':mobile' => $username
        ]);

        if($isActive){
            $query->andWhere([
                'users.is_active' => DatabaseHelper::ACTIVE,
            ]);
        }

        return $query->one();
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'is_active' => DatabaseHelper::ACTIVE]);
    }

    public function SaveCompanyUser(){
        $this->user_role = DatabaseHelper::COMPANY_USER;

        if($this->save()){
            $model = new CompanyUsers();
            $model->user = $this->id;
            $model->company = Auction::$app->session->get('user.company');

            $model->save();
            return true;
        }

        return false;
    }

}
