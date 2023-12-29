<?php
/**
 * @author Dyson_AmastyCheckoutExtension
 * @package Dyson_AmastyCheckoutExtension
 */


namespace Dyson\AmastyCheckoutExtension\Setup;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    protected $helperData;
    /**
     * __construct function
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param \Dyson\AmastyCheckoutExtension\Helper\Data $helperData
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Dyson\AmastyCheckoutExtension\Helper\Data $helperData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $dyson_city = $setup->getTable('dyson_city');

        if (version_compare($context->getVersion(), '1.0.3', '<')) {

                $dataTH = $this->importDataIntoTheTable();
                $countryCode = $this->helperData->getCountryCode();
            if ($dyson_city && $countryCode == "TH") {
                $setup->getConnection()->insertArray(
                    $dyson_city,
                    ["country_code","region_id","store_id","city"],    //column names
                    $dataTH
                );
            }

                $dataSA = $this->importDataIntoTheSA();
            if ($dyson_city && $countryCode == "SA") {
                $setup->getConnection()->insertArray(
                    $dyson_city,
                    ["country_code","region_id","store_id","city"],    //column names
                    $dataSA
                );
            }
        }
    }
    /**
     * importDataIntoTheTable function
     *
     * @return array
     */
    private function importDataIntoTheTable()
    {

        // phpcs:ignore
        $thailandData = [["TH",null,2,"Krung Thep Maha Nakhon (Bangkok Metropolis)"], ["TH",null,2,"Krabi"], ["TH",null,2,"Kanchanaburi"], ["TH",null,2,"Kalasin"], ["TH",null,2,"Kamphaeng Phet"], ["TH",null,2,"Khon Kaen"], ["TH",null,2,"Chanthaburi"], ["TH",null,2,"Chachoengsao"], ["TH",null,2,"Chonburi"], ["TH",null,2,"Chainat"], ["TH",null,2,"Chaiyaphum"], ["TH",null,2,"Chumphon"], ["TH",null,2,"Trang"], ["TH",null,2,"Trat"], ["TH",null,2,"Tak"], ["TH",null,2,"Nakhon Nayok"], ["TH",null,2,"Nakhon Pathom"], ["TH",null,2,"Nakhon Phanom"], ["TH",null,2,"Nakhon Ratchasima"], ["TH",null,2,"Nakhon Si Thammarat"], ["TH",null,2,"Nakhon Sawan"], ["TH",null,2,"Nonthaburi"], ["TH",null,2,"Narathiwat"], ["TH",null,2,"Nan"], ["TH",null,2,"Bueng Kan"], ["TH",null,2,"Buriram"], ["TH",null,2,"Pathum Thani"], ["TH",null,2,"Prachuap Khiri Khan"], ["TH",null,2,"Prachinburi"], ["TH",null,2,"Pattani"], ["TH",null,2,"Phra Nakhon Si Ayutthaya"], ["TH",null,2,"Phayao"], ["TH",null,2,"Phang Nga"], ["TH",null,2,"Phatthalung"], ["TH",null,2,"Phichit"], ["TH",null,2,"Phitsanulok"], ["TH",null,2,"Phuket"], ["TH",null,2,"Maha Sarakham"], ["TH",null,2,"Mukdahan"], ["TH",null,2,"Yala"], ["TH",null,2,"Yasothon"], ["TH",null,2,"Ranong"], ["TH",null,2,"Rayong"], ["TH",null,2,"Ratchaburi"], ["TH",null,2,"Roi Et"], ["TH",null,2,"Lopburi"], ["TH",null,2,"Lampang"], ["TH",null,2,"Lamphun"], ["TH",null,2,"Sisaket"], ["TH",null,2,"Sakon Nakhon"], ["TH",null,2,"Songkhla"], ["TH",null,2,"Satun"], ["TH",null,2,"Samut Prakan"], ["TH",null,2,"Samut Songkhram"], ["TH",null,2,"Samut Sakhon"], ["TH",null,2,"Saraburi"], ["TH",null,2,"Sa Kaeo"], ["TH",null,2,"Sing Buri"], ["TH",null,2,"Suphan Buri"], ["TH",null,2,"Surat Thani"], ["TH",null,2,"Surin"], ["TH",null,2,"Sukhothai"], ["TH",null,2,"Nong Khai"], ["TH",null,2,"Nong Bua Lamphu"], ["TH",null,2,"Amnat Charoen"], ["TH",null,2,"Udon Thani"], ["TH",null,2,"Uttaradit"], ["TH",null,2,"Uthai Thani"], ["TH",null,2,"Ubon Ratchathani"], ["TH",null,2,"Ang Thong"], ["TH",null,2,"Chiang Rai"], ["TH",null,2,"Chiang Mai"], ["TH",null,2,"Phetchaburi"], ["TH",null,2,"Phetchabun"], ["TH",null,2,"Loei"], ["TH",null,2,"Phrae"], ["TH",null,2,"Mae Hong Son"], ["TH",null,1,"กรุงเทพมหานคร"], ["TH",null,1,"กระบี่"], ["TH",null,1,"กาญจนบุรี"], ["TH",null,1,"กาฬสินธุ์"], ["TH",null,1,"กำแพงเพชร"], ["TH",null,1,"ขอนแก่น"], ["TH",null,1,"จันทบุรี"], ["TH",null,1,"ฉะเชิงเทรา"], ["TH",null,1,"ชลบุรี"], ["TH",null,1,"ชัยนาท"], ["TH",null,1,"ชัยภูมิ"], ["TH",null,1,"ชุมพร"], ["TH",null,1,"ตรัง"], ["TH",null,1,"ตราด"], ["TH",null,1,"ตาก"], ["TH",null,1,"นครนายก"], ["TH",null,1,"นครปฐม"], ["TH",null,1,"นครพนม"], ["TH",null,1,"นครราชสีมา"], ["TH",null,1,"นครศรีธรรมราช"], ["TH",null,1,"นครสวรรค์"], ["TH",null,1,"นนทบุรี"], ["TH",null,1,"นราธิวาส"], ["TH",null,1,"น่าน"], ["TH",null,1,"บึงกาฬ"], ["TH",null,1,"บุรีรัมย์"], ["TH",null,1,"ปทุมธานี"], ["TH",null,1,"ประจวบคีรีขันธ์"], ["TH",null,1,"ปราจีนบุรี"], ["TH",null,1,"ปัตตานี"], ["TH",null,1,"พระนครศรีอยุธยา"], ["TH",null,1,"พะเยา"], ["TH",null,1,"พังงา"], ["TH",null,1,"พัทลุง"], ["TH",null,1,"พิจิตร"], ["TH",null,1,"พิษณุโลก"], ["TH",null,1,"ภูเก็ต"], ["TH",null,1,"มหาสารคาม"], ["TH",null,1,"มุกดาหาร"], ["TH",null,1,"ยะลา"], ["TH",null,1,"ยโสธร"], ["TH",null,1,"ระนอง"], ["TH",null,1,"ระยอง"], ["TH",null,1,"ราชบุรี"], ["TH",null,1,"ร้อยเอ็ด"], ["TH",null,1,"ลพบุรี"], ["TH",null,1,"ลำปาง"], ["TH",null,1,"ลำพูน"], ["TH",null,1,"ศรีสะเกษ"], ["TH",null,1,"สกลนคร"], ["TH",null,1,"สงขลา"], ["TH",null,1,"สตูล"], ["TH",null,1,"สมุทรปราการ"], ["TH",null,1,"สมุทรสงคราม"], ["TH",null,1,"สมุทรสาคร"], ["TH",null,1,"สระบุรี"], ["TH",null,1,"สระแก้ว"], ["TH",null,1,"สิงห์บุรี"], ["TH",null,1,"สุพรรณบุรี"], ["TH",null,1,"สุราษฎร์ธานี"], ["TH",null,1,"สุรินทร์"], ["TH",null,1,"สุโขทัย"], ["TH",null,1,"หนองคาย"], ["TH",null,1,"หนองบัวลำภู"], ["TH",null,1,"อำนาจเจริญ"], ["TH",null,1,"อุดรธานี"], ["TH",null,1,"อุตรดิตถ์"], ["TH",null,1,"อุทัยธานี"], ["TH",null,1,"อุบลราชธานี"], ["TH",null,1,"อ่างทอง"], ["TH",null,1,"เชียงราย"], ["TH",null,1,"เชียงใหม่"], ["TH",null,1,"เพชรบุรี"], ["TH",null,1,"เพชรบูรณ์"], ["TH",null,1,"เลย"], ["TH",null,1,"แพร่"], ["TH",null,1,"แม่ฮ่องสอน"]];

        return $thailandData;
    }
    /**
     * importDataIntoTheSA function
     *
     * @return array
     */
    private function importDataIntoTheSA()
    {
        // phpcs:ignore
        $saData = [["SA",null,2,"Riyadh"],["SA",null,2,"Jeddah"],["SA",null,2,"Makkah"],["SA",null,2,"Madinah"],["SA",null,2,"Dammam"],["SA",null,2,"Abha"],["SA",null,2,"Abu Arish"],["SA",null,2,"Ad Darb"],["SA",null,2,"Adham"],["SA",null,2,"Afif"],["SA",null,2,"Aflaj"],["SA",null,2,"Ahad Rufaidah"],["SA",null,2,"Al Bikeryah"],["SA",null,2,"Al Dayer"],["SA",null,2,"Al Hassa"],["SA",null,2,"Al Hufuf"],["SA",null,2,"Al Khurmah"],["SA",null,2,"Al Midhnab"],["SA",null,2,"Al Mukhwah"],["SA",null,2,"Al Qarya Al Ulya"],["SA",null,2,"Al Qonfotha"],["SA",null,2,"Al Quwayiyah"],["SA",null,2,"Al Ula"],["SA",null,2,"Al-Maima'ah"],["SA",null,2,"An Nairiyah"],["SA",null,2,"An Namas"],["SA",null,2,"Anak"],["SA",null,2,"Aqiq"],["SA",null,2,"Ar Rass"],["SA",null,2,"Arar"],["SA",null,2,"At Tuwal"],["SA",null,2,"Badaya"],["SA",null,2,"Badr"],["SA",null,2,"Baha"],["SA",null,2,"Baysh"],["SA",null,2,"Biljurashi"],["SA",null,2,"Bisha"],["SA",null,2,"Buqaiq"],["SA",null,2,"Buraydah"],["SA",null,2,"Dawadmi"],["SA",null,2,"Dawmat Al Jandal"],["SA",null,2,"Dere'iyeh"],["SA",null,2,"Dhahran"],["SA",null,2,"Dhahran Aljanoub"],["SA",null,2,"Dhuba"],["SA",null,2,"Gizan"],["SA",null,2,"Hafer Al Batin"],["SA",null,2,"Hail"],["SA",null,2,"Hanakiyah"],["SA",null,2,"Hawtat Bani Tamim"],["SA",null,2,"Jouf"],["SA",null,2,"Jubail"],["SA",null,2,"Jumom"],["SA",null,2,"Khafji"],["SA",null,2,"Khamis Mushait"],["SA",null,2,"Kharj"],["SA",null,2,"Khobar"],["SA",null,2,"Khulais"],["SA",null,2,"Lith"],["SA",null,2,"Mahd Ad Dhahab"],["SA",null,2,"Majardah"],["SA",null,2,"Mandaq"],["SA",null,2,"Muhayyil Assir"],["SA",null,2,"Muzamiyah"],["SA",null,2,"Najran"],["SA",null,2,"Qassim"],["SA",null,2,"Qatif"],["SA",null,2,"Qilwah"],["SA",null,2,"Qurayat"],["SA",null,2,"Rabigh"],["SA",null,2,"Rafaya AlGimsh"],["SA",null,2,"Rafha"],["SA",null,2,"Raj'l Almaa"],["SA",null,2,"Ranyah"],["SA",null,2,"Ras Tanura"],["SA",null,2,"Riyadh Al Khabra"],["SA",null,2,"Rumah"],["SA",null,2,"Sabt Al Alaya"],["SA",null,2,"Sabya"],["SA",null,2,"Safwa"],["SA",null,2,"Sajir"],["SA",null,2,"Samtah"],["SA",null,2,"Sarat Abida"],["SA",null,2,"Seihat"],["SA",null,2,"Shaqra"],["SA",null,2,"Sharourah"],["SA",null,2,"Skaka"],["SA",null,2,"Sulayel"],["SA",null,2,"Tabarjal"],["SA",null,2,"Tabuk"],["SA",null,2,"Taif"],["SA",null,2,"Taima"],["SA",null,2,"Tanumah"],["SA",null,2,"Tareeb"],["SA",null,2,"Tarut"],["SA",null,2,"Tathleeth"],["SA",null,2,"Thoqba"],["SA",null,2,"Turaif"],["SA",null,2,"Turbah"],["SA",null,2,"Umm Lajj"],["SA",null,2,"Unayzah"],["SA",null,2,"Uyun Al Jiwa"],["SA",null,2,"Wadi El Dwaser"],["SA",null,2,"Wajh"],["SA",null,2,"Yanbu"],["SA",null,2,"Zilfi"],["SA",null,1,"الرياض"],["SA",null,1,"جدة"],["SA",null,1,"مكة المكرمة"],["SA",null,1,"المدينة المنورة"],["SA",null,1,"الدمام"],["SA",null,1,"أبها"],["SA",null,1,"ابو عريش"],["SA",null,1,"الدرب"],["SA",null,1,"اضم "],["SA",null,1,"عفيف"],["SA",null,1,"الأفلاج"],["SA",null,1,"احد رفيدة"],["SA",null,1,"البكيرية"],["SA",null,1,"الدائر "],["SA",null,1,"الأحساء"],["SA",null,1,"الهفوف"],["SA",null,1,"الخرمة"],["SA",null,1,"المذنب"],["SA",null,1,"المخواه"],["SA",null,1,"القرية العليا"],["SA",null,1,"القنفذة"],["SA",null,1,"القويعيه"],["SA",null,1,"العلا"],["SA",null,1,"المجمعة "],["SA",null,1,"النعيرية"],["SA",null,1,"النماص  "],["SA",null,1,"عنك"],["SA",null,1,"العقيق"],["SA",null,1,"الرس"],["SA",null,1,"عرعر"],["SA",null,1,"الطوال"],["SA",null,1,"البدايع"],["SA",null,1,"بدر"],["SA",null,1,"الباحة"],["SA",null,1,"بيش "],["SA",null,1,"بلجرشي"],["SA",null,1,"بيشة"],["SA",null,1,"بقيق"],["SA",null,1,"بريدة"],["SA",null,1,"الدوادمي"],["SA",null,1,"دومة الجندل"],["SA",null,1,"الدرعية "],["SA",null,1,"الظهران"],["SA",null,1,"الظهران الجنوب"],["SA",null,1,"ضبا"],["SA",null,1,"جيزان"],["SA",null,1,"حفر الباطن"],["SA",null,1,"حائل"],["SA",null,1,"الحناكية "],["SA",null,1,"حوطة بني تميم"],["SA",null,1,"الجوف"],["SA",null,1,"جبيل"],["SA",null,1,"الجموم "],["SA",null,1,"الخفجي"],["SA",null,1,"خميس مشيط"],["SA",null,1,"الخرج"],["SA",null,1," الخبر"],["SA",null,1,"خليص"],["SA",null,1,"الليث "],["SA",null,1,"مهد الذهب"],["SA",null,1,"المجارده  "],["SA",null,1,"المندق "],["SA",null,1,"محايل عسير"],["SA",null,1,"المزاحمية "],["SA",null,1,"نجران"],["SA",null,1,"القصيم   "],["SA",null,1,"قطيف"],["SA",null,1,"قلوة "],["SA",null,1,"القريات"],["SA",null,1,"رابغ"],["SA",null,1,"رفايع الجمش "],["SA",null,1,"رفحة "],["SA",null,1,"رجال ألمع "],["SA",null,1,"رانيا "],["SA",null,1,"راس تنورة "],["SA",null,1,"رياض الخبراء"],["SA",null,1,"رماح"],["SA",null,1,"سبت العلايا"],["SA",null,1,"صبيا"],["SA",null,1,"صفوى "],["SA",null,1,"ساجر "],["SA",null,1,"صامطه"],["SA",null,1,"سراة عبيدة"],["SA",null,1,"سيهات"],["SA",null,1,"شقراء"],["SA",null,1,"شرورة"],["SA",null,1,"سكاكا"],["SA",null,1,"السليل"],["SA",null,1,"طبرجل"],["SA",null,1,"تبوك"],["SA",null,1,"الطائف"],["SA",null,1,"تيماء"],["SA",null,1,"تنومة"],["SA",null,1,"طريب "],["SA",null,1,"تاروت"],["SA",null,1,"تثليث"],["SA",null,1,"الثقبة "],["SA",null,1,"طريف"],["SA",null,1,"تربه "],["SA",null,1,"املج"],["SA",null,1,"عنيزة"],["SA",null,1,"عيون الجواء"],["SA",null,1,"وادي الدواسر"],["SA",null,1,"الوجه"],["SA",null,1,"ينبع"],["SA",null,1,"الزلفي"]];

        return $saData;
    }
}
