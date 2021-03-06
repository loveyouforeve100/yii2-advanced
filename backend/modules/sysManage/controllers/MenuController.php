<?php

namespace backend\modules\sysManage\controllers;

use backend\modules\sysManage\components\MenuHelper;
use Yii;
use backend\modules\sysManage\models\Menu;
use backend\modules\sysManage\models\searchs\Menu as MenuSearch;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\modules\sysManage\components\Helper;

/**
 * MenuController implements the CRUD actions for Menu model.
 *
 */
class MenuController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Menu models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MenuSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        if (Yii::$app->request->isAjax) {
            Yii::$app->getResponse()->format = 'json';
            $id = intval(Yii::$app->request->post('id'));
            $menuRows = MenuHelper::getMenu(Yii::$app->user->id, $id);
            $menus = [
                'status' => 'OK',
                'data' => null,
            ];
            if ($menuRows) {
                foreach ($menuRows as $key => $menuRow) {
                    if (isset($menuRow['items'])) {
                        $menus['data'][$key]['name'] = $menuRow['label'] . ' <span title="排序"> <i class="fa fa-sort"> </i> ' . $menuRow['order'] . '</span><div class="tree-actions"><a href="' . Url::toRoute(['update', 'id' => $menuRow['id']]) . '"><i class="fa fa-edit"></i></a> <a href="' . Url::toRoute(['delete', 'id' => $menuRow['id']]) . '" data-confirm="您确定要删除此项吗？" data-method="post" data-pjax="0"><i class="fa fa-trash-o"></i></a> <i class="fa fa-refresh"></i></div>';
                        $menus['data'][$key]['type'] = 'folder';
                        $menus['data'][$key]['additionalParameters'] = ['id' => $menuRow['id'], 'children' => true];
                    } else {
                        $menus['data'][$key]['name'] = $menuRow['label'] . ' <span title="排序"> <i class="fa fa-sort"> </i> ' . $menuRow['order'] . '</span><div class="tree-actions"><a href="' . Url::toRoute(['update', 'id' => $menuRow['id']]) . '"><i class="fa fa-edit"></i></a> <a href="' . Url::toRoute(['delete', 'id' => $menuRow['id']]) . '" data-confirm="您确定要删除此项吗？" data-method="post" data-pjax="0"><i class="fa fa-trash-o"></i></a> <i class="fa fa-refresh"></i></div>';
                        $menus['data'][$key]['type'] = 'item';
                        $menus['data'][$key]['additionalParameters'] = ['id' => $menuRow['id']];
                    }
                }
            }
            return $menus;
        }

        return $this->render('index', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Menu model.
     * @param  integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
                'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Menu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Menu;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Helper::invalidate();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                    'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Menu model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param  integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->menuParent) {
            $model->parent_name = $model->menuParent->name;
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Helper::invalidate();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                    'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Menu model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param  integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Helper::invalidate();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Menu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param  integer $id
     * @return Menu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Menu::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
