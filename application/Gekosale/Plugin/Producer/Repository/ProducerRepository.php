<?php
/*
 * Gekosale Open-Source E-Commerce Platform
 *
 * This file is part of the Gekosale package.
 *
 * (c) Adam Piotrowski <adam@gekosale.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */
namespace Gekosale\Plugin\Producer\Repository;

use Gekosale\Core\Repository;
use Gekosale\Core\Model\Producer;
use Gekosale\Core\Model\ProducerTranslation;

/**
 * Class ProducerRepository
 *
 * @package Gekosale\Plugin\Producer\Repository
 * @author  Adam Piotrowski <adam@gekosale.com>
 */
class ProducerRepository extends Repository
{
    /**
     * Returns producer collection
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all()
    {
        return Producer::all();
    }

    /**
     * Returns single producer model
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|static
     */
    public function find($id)
    {
        return Producer::with('translation')->findOrFail($id);
    }

    /**
     * Deletes producer
     *
     * @param array|int $id
     */
    public function delete($id)
    {
        $this->transaction(function () use ($id) {
            return Producer::destroy($id);
        });
    }

    /**
     * Saves producer model
     *
     * @param array    $Data Submitted form data
     * @param int|null $id   Producer ID or null if new producer
     */
    public function save(array $Data, $id = null)
    {
        $this->transaction(function () use ($Data, $id) {
            $producer = Producer::firstOrNew([
                'id' => $id
            ]);

            $producer->save();

            foreach ($this->getLanguageIds() as $language) {

                $translation = ProducerTranslation::firstOrCreate([
                    'producer_id' => $producer->id,
                    'language_id' => $language
                ]);

                $translation->setTranslationData($Data, $language);
                $translation->save();
            }
        });
    }

    /**
     * Returns array containing values needed to populate the form
     *
     * @param int $id Producer ID
     *
     * @return array Populate data
     */
    public function getPopulateData($id)
    {
        $producerData = $this->find($id);
        $populateData = [];
        $accessor     = $this->getPropertyAccessor();
        $languageData = $producerData->getTranslationData();

        $accessor->setValue($populateData, '[required_data]', [
            'language_data' => $languageData
        ]);

        $accessor->setValue($populateData, '[description_data]', [
            'language_data' => $languageData
        ]);

        $accessor->setValue($populateData, '[meta_data]', [
            'language_data' => $languageData
        ]);

        return $populateData;
    }
}