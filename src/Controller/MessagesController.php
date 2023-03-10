<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Messages Controller
 *
 * @property \App\Model\Table\MessagesTable $Messages
 * @method \App\Model\Entity\Message[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MessagesController extends AppController
{

    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('basic_layout');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $conditions = array('Messages.status' => 1, 'Users.status' => 1);
        $this->paginate = [
            'contain' => ['Users', 'Categories'],
            'conditions' => $conditions
        ];
        $messages = $this->paginate($this->Messages);
        $auth_user_id = $this->Auth->user('id');
        $this->set(compact('messages', 'auth_user_id'));
     }

    /**
     * View method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $message = $this->Messages->get($id, [
            'contain' => ['Users', 'Categories'],
        ]);
        if($message->status !== 1 or $message->user->status !== 1) {
            $this->render('/Original/invalid');
            return;
        }
        $this->set(compact('message'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $message = $this->Messages->newEmptyEntity();
        if ($this->request->is('post')) {
            $message = $this->Messages->patchEntity($message, $this->request->getData());
            $message->status = 1;
            $message->user_id = $this->Auth->user('id'); 
            if ($this->Messages->save($message)) {
                $this->Flash->success(__('The message has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The message could not be saved. Please, try again.'));
        }
        // $users = $this->Messages->Users->find('list', ['limit' => 200])->all();
        $categories = $this->Messages->Categories->find('list', ['limit' => 200])->all();
        $this->set(compact('message', 'categories'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $message = $this->Messages->get($id, [
            'contain' => ['Users'],
        ]);
        if($message->status !== 1 or $message->user->status !== 1 or $message->user_id !== $this->Auth->user('id')) {
            $this->render('/Original/invalid');
            return;
        }
        if ($this->request->is(['patch', 'post', 'put'])) {
            $message = $this->Messages->patchEntity($message, $this->request->getData());
            if ($this->Messages->save($message)) {
                $this->Flash->success(__('The message has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The message could not be saved. Please, try again.'));
        }
        $users = $this->Messages->Users->find('list', ['limit' => 200])->all();
        $categories = $this->Messages->Categories->find('list', ['limit' => 200])->all();
        $this->set(compact('message', 'users', 'categories'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $message = $this->Messages->get($id, ['contain' => ['Users']]);
        if($message->status !== 1 or $message->user->status !== 1 or $message->user_id !== $this->Auth->user('id')) {
            $this->render('/Original/invalid');
            return;
        }
        $message->status = 2;
        if ($this->Messages->save($message)) {
            $this->Flash->success(__('The message has been deleted.'));
        } else {
            $this->Flash->error(__('The message could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function isAuthorized($user)
    {
        return true;
    }
}
