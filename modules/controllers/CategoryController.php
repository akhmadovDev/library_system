<?php

namespace app\modules\controllers;

use app\models\Category;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends RoleController
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Category models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $query = Category::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Category model.
     * @param int $id ID
     * @return string
     * @throws Exception
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findCategory($id),
        ]);
    }

    /**
     * Creates a new Category model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Category();

        if ($this->request->isPost) {

            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Kategoriya qo\'shildi');
                return $this->redirect(['index']);
            }

        } else {
            $model->loadDefaultValues();
        }


        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Kategoriya o\'zgartirildi');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response
    {
        $model = $this->findModel($id);
        $model->status = $model->status === Category::STATUS_ACTIVE ? Category::STATUS_INACTIVE : Category::STATUS_ACTIVE;
        if ($model->save()) {
            $alert_message = $model->status === Category::STATUS_INACTIVE ? 'Kategoriya o\'chirildi' : 'Kategoriya qayta tiklandi';
            Yii::$app->session->setFlash('success', $alert_message);
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Category
    {
        if (($model = Category::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @throws Exception
     */
    private function findCategory(int $id)
    {
        $sql = 'SELECT category.*, (SELECT COUNT(*) FROM {{book}} WHERE [[category.id]] = [[book.category_id]] AND status = :status) AS book_count FROM {{category}} WHERE status = :status AND id = :id';
        $query = Yii::$app->db->createCommand($sql);
        $query->bindValue(':status', Category::STATUS_ACTIVE);
        $query->bindValue(':id', $id);
        return $query->queryOne();
    }
}
