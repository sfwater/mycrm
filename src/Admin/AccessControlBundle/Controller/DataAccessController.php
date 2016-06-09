<?php

namespace Admin\AccessControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Admin\AdminAclController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
/**
* @Route("/admin/acls")
*/
class DataAccessController extends AdminAclController
{
    /**
     * 所有访问控制列表
     * @Route(
     *      "/datas", name="admin_acls_datas_index",
     *      options = {"name":"数据控制","description":"建立用户关系实现数据访问控制","category":"访问控制","order":2, "show":true}
     *   )
     * @Method("GET") 
     * @Template("AdminAccessControlBundle:DataAccess:index.html.twig")
     */
    public function indexAction()
    {
        $users = $this->getSystemAvaliableUsers();

        $groups = array();

        foreach ($users as $key => $value) {
            $name = $value->getNickname();
            $index = $this->getFirstCharter($name);
            if( !in_array($index, $groups) ){
                $groups[] = $index;
            }
        }
        sort($groups);

        $groupedUsers = array();
        foreach ($groups as $group) {
            $groupedUsers[$group] = array();
            foreach ($users as $key => $value) {
                $index = $this->getFirstCharter($value->getNickname());
                if($index == $group){
                    $groupedUsers[$group][] = $this->resolveUser($value);
                }
            }
        }

        //按照拼音分组排序用户
        return array('result'=>$groupedUsers);
    }
    /**
     * 查询用户关系
     * @Route(
     *      "/relations/", name="admin_acls_relations",
     *      options = {"name":"用户关系","description":"查看用户的关系","category":"访问控制","order":3}
     *   )
     * @Method("POST")
     */
    public function relationsAction(Request $request)
    {
        $userId = intval($request->request->get('userId'));
        $em = $this->getDoctrine()->getManager();




        //如果是查询用户的权限
        if( $userId > 0 ){
            $dql = 'SELECT dist FROM AdminUserBundle:User dist WHERE dist.id=:id';
            $query = $em->createQuery($dql)->setParameters(array('id'=>$userId));
            $one = $query->getOneOrNullResult();

            if( !$one ){
                throw new \Exception($username);
            }
            $result = $em->getRepository('AdminAccessControlBundle:UserTree')->findByParentId($one->getId());

            $arr = array();
            foreach ($result as $key => $value) {
                $arr[] = $this->resolveUserTree($value);
            }

            return $this->jsonResponse($arr);
        }

        return $this->jsonResponse(array());
    }
    /**
     * 保存用户、用户组的权限
     * @Route(
     *      "/relations/", name="admin_acls_relations_save",
     *      options = {"name":"保存关系","description":"保存用户的关系","category":"访问控制","order":4}
     *   )
     * @Method("DELETE")
     */
    public function relationsSaveAction(Request $request)
    {
        $to = $request->query->get('to');
        $action = $request->request->get('action');
        $ids = $request->request->get('ids');

        if( empty($to) || empty($action) ){
            $this->throwException('Params error');
        }

        $em = $this->getDoctrine()->getManager();
        //如果是给用户分配权限
        $toUser = $em->getRepository('AdminUserBundle:User')->loadUserByUsername($to);
        if( !$toUser ){
            $this->throwException('User not found');
        }
        $dql = "DELETE FROM AdminAccessControlBundle:UserTree dist WHERE dist.parentId=:userId";
        $query = $em->createQuery($dql)->setParameter("userId", $toUser->getId());
        $query->execute();


        foreach ($ids as $value) {
            $entity = new UserTree();
            $entity->setParentId($toUser->getId());
            $entity->setUserId($value);
            $em->persist($entity);
            $em->flush();
        }



        return $this->success();
    }
    private function resolveUser($item){
        $result = array();
        $result["username"] = $item->getUsername();
        $result["nickname"] = $item->getNickname();
        $result["id"] = $item->getId();
        return $result;
    }
    private function resolveUserTree($item){
        $result = array();
        $result["userId"] = $item->getUserId();
        $result["parentId"] = $item->getParentId();
        $result["id"] = $item->getId();
        return $result;
    }
    private function getFirstCharter($str){
        if(empty($str)){return '';}
        $fchar=ord($str{0});
        if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
        $s1=iconv('UTF-8','gb2312',$str);
        $s2=iconv('gb2312','UTF-8',$s1);
        $s=$s2==$str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319&&$asc<=-20284) return 'A';
        if($asc>=-20283&&$asc<=-19776) return 'B';
        if($asc>=-19775&&$asc<=-19219) return 'C';
        if($asc>=-19218&&$asc<=-18711) return 'D';
        if($asc>=-18710&&$asc<=-18527) return 'E';
        if($asc>=-18526&&$asc<=-18240) return 'F';
        if($asc>=-18239&&$asc<=-17923) return 'G';
        if($asc>=-17922&&$asc<=-17418) return 'H';
        if($asc>=-17417&&$asc<=-16475) return 'J';
        if($asc>=-16474&&$asc<=-16213) return 'K';
        if($asc>=-16212&&$asc<=-15641) return 'L';
        if($asc>=-15640&&$asc<=-15166) return 'M';
        if($asc>=-15165&&$asc<=-14923) return 'N';
        if($asc>=-14922&&$asc<=-14915) return 'O';
        if($asc>=-14914&&$asc<=-14631) return 'P';
        if($asc>=-14630&&$asc<=-14150) return 'Q';
        if($asc>=-14149&&$asc<=-14091) return 'R';
        if($asc>=-14090&&$asc<=-13319) return 'S';
        if($asc>=-13318&&$asc<=-12839) return 'T';
        if($asc>=-12838&&$asc<=-12557) return 'W';
        if($asc>=-12556&&$asc<=-11848) return 'X';
        if($asc>=-11847&&$asc<=-11056) return 'Y';
        if($asc>=-11055&&$asc<=-10247) return 'Z';
        return null;
    }
}
