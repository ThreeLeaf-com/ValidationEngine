<?php

namespace ThreeLeaf\ValidationEngine\Enums;

/**
 * Enum for representing times of the day in 15-minute intervals.
 * Includes functions to retrieve specific intervals such as whole hours, half-hours, and 15-minute increments.
 *
 * @OA\Schema(
 *     schema="TimeOfDay",
 *     type="string",
 *     enum={
 *         "00:00", "00:15", "00:30", "00:45", "01:00", "01:15", "01:30", "01:45",
 *         "02:00", "02:15", "02:30", "02:45", "03:00", "03:15", "03:30", "03:45",
 *         "04:00", "04:15", "04:30", "04:45", "05:00", "05:15", "05:30", "05:45",
 *         "06:00", "06:15", "06:30", "06:45", "07:00", "07:15", "07:30", "07:45",
 *         "08:00", "08:15", "08:30", "08:45", "09:00", "09:15", "09:30", "09:45",
 *         "10:00", "10:15", "10:30", "10:45", "11:00", "11:15", "11:30", "11:45",
 *         "12:00", "12:15", "12:30", "12:45", "13:00", "13:15", "13:30", "13:45",
 *         "14:00", "14:15", "14:30", "14:45", "15:00", "15:15", "15:30", "15:45",
 *         "16:00", "16:15", "16:30", "16:45", "17:00", "17:15", "17:30", "17:45",
 *         "18:00", "18:15", "18:30", "18:45", "19:00", "19:15", "19:30", "19:45",
 *         "20:00", "20:15", "20:30", "20:45", "21:00", "21:15", "21:30", "21:45",
 *         "22:00", "22:15", "22:30", "22:45", "23:00", "23:15", "23:30", "23:45"
 *     },
 *     description="Represents 15-minute intervals of the day"
 * )
 */
enum TimeOfDay: string
{
    case T00_00 = '00:00';
    case T00_15 = '00:15';
    case T00_30 = '00:30';
    case T00_45 = '00:45';
    case T01_00 = '01:00';
    case T01_15 = '01:15';
    case T01_30 = '01:30';
    case T01_45 = '01:45';
    case T02_00 = '02:00';
    case T02_15 = '02:15';
    case T02_30 = '02:30';
    case T02_45 = '02:45';
    case T03_00 = '03:00';
    case T03_15 = '03:15';
    case T03_30 = '03:30';
    case T03_45 = '03:45';
    case T04_00 = '04:00';
    case T04_15 = '04:15';
    case T04_30 = '04:30';
    case T04_45 = '04:45';
    case T05_00 = '05:00';
    case T05_15 = '05:15';
    case T05_30 = '05:30';
    case T05_45 = '05:45';
    case T06_00 = '06:00';
    case T06_15 = '06:15';
    case T06_30 = '06:30';
    case T06_45 = '06:45';
    case T07_00 = '07:00';
    case T07_15 = '07:15';
    case T07_30 = '07:30';
    case T07_45 = '07:45';
    case T08_00 = '08:00';
    case T08_15 = '08:15';
    case T08_30 = '08:30';
    case T08_45 = '08:45';
    case T09_00 = '09:00';
    case T09_15 = '09:15';
    case T09_30 = '09:30';
    case T09_45 = '09:45';
    case T10_00 = '10:00';
    case T10_15 = '10:15';
    case T10_30 = '10:30';
    case T10_45 = '10:45';
    case T11_00 = '11:00';
    case T11_15 = '11:15';
    case T11_30 = '11:30';
    case T11_45 = '11:45';
    case T12_00 = '12:00';
    case T12_15 = '12:15';
    case T12_30 = '12:30';
    case T12_45 = '12:45';
    case T13_00 = '13:00';
    case T13_15 = '13:15';
    case T13_30 = '13:30';
    case T13_45 = '13:45';
    case T14_00 = '14:00';
    case T14_15 = '14:15';
    case T14_30 = '14:30';
    case T14_45 = '14:45';
    case T15_00 = '15:00';
    case T15_15 = '15:15';
    case T15_30 = '15:30';
    case T15_45 = '15:45';
    case T16_00 = '16:00';
    case T16_15 = '16:15';
    case T16_30 = '16:30';
    case T16_45 = '16:45';
    case T17_00 = '17:00';
    case T17_15 = '17:15';
    case T17_30 = '17:30';
    case T17_45 = '17:45';
    case T18_00 = '18:00';
    case T18_15 = '18:15';
    case T18_30 = '18:30';
    case T18_45 = '18:45';
    case T19_00 = '19:00';
    case T19_15 = '19:15';
    case T19_30 = '19:30';
    case T19_45 = '19:45';
    case T20_00 = '20:00';
    case T20_15 = '20:15';
    case T20_30 = '20:30';
    case T20_45 = '20:45';
    case T21_00 = '21:00';
    case T21_15 = '21:15';
    case T21_30 = '21:30';
    case T21_45 = '21:45';
    case T22_00 = '22:00';
    case T22_15 = '22:15';
    case T22_30 = '22:30';
    case T22_45 = '22:45';
    case T23_00 = '23:00';
    case T23_15 = '23:15';
    case T23_30 = '23:30';
    case T23_45 = '23:45';

    /**
     * Get an array of TimeOfDay enum cases representing each hour (00:00, 01:00, etc.).
     *
     * @return array
     */
    public static function hours(): array
    {
        return [
            self::T00_00, self::T01_00, self::T02_00, self::T03_00, self::T04_00, self::T05_00,
            self::T06_00, self::T07_00, self::T08_00, self::T09_00, self::T10_00, self::T11_00,
            self::T12_00, self::T13_00, self::T14_00, self::T15_00, self::T16_00, self::T17_00,
            self::T18_00, self::T19_00, self::T20_00, self::T21_00, self::T22_00, self::T23_00,
        ];
    }

    /**
     * Get an array of TimeOfDay enum cases representing half-hour intervals (00:30, 01:30, etc.).
     *
     * @return array
     */
    public static function minutes30(): array
    {
        return [
            self::T00_00, self::T00_30, self::T01_00, self::T01_30, self::T02_00, self::T02_30,
            self::T03_00, self::T03_30, self::T04_00, self::T04_30, self::T05_00, self::T05_30,
            self::T06_00, self::T06_30, self::T07_00, self::T07_30, self::T08_00, self::T08_30,
            self::T09_00, self::T09_30, self::T10_00, self::T10_30, self::T11_00, self::T11_30,
            self::T12_00, self::T12_30, self::T13_00, self::T13_30, self::T14_00, self::T14_30,
            self::T15_00, self::T15_30, self::T16_00, self::T16_30, self::T17_00, self::T17_30,
            self::T18_00, self::T18_30, self::T19_00, self::T19_30, self::T20_00, self::T20_30,
            self::T21_00, self::T21_30, self::T22_00, self::T22_30, self::T23_00, self::T23_30,
        ];
    }

    /**
     * Get an array of TimeOfDay enum cases representing 15-minute intervals (00:15, 00:45, etc.).
     *
     * @return array
     */
    public static function minutes15(): array
    {
        return [
            self::T00_00, self::T00_15, self::T00_30, self::T00_45, self::T01_00, self::T01_15,
            self::T01_30, self::T01_45, self::T02_00, self::T02_15, self::T02_30, self::T02_45,
            self::T03_00, self::T03_15, self::T03_30, self::T03_45, self::T04_00, self::T04_15,
            self::T04_30, self::T04_45, self::T05_00, self::T05_15, self::T05_30, self::T05_45,
            self::T06_00, self::T06_15, self::T06_30, self::T06_45, self::T07_00, self::T07_15,
            self::T07_30, self::T07_45, self::T08_00, self::T08_15, self::T08_30, self::T08_45,
            self::T09_00, self::T09_15, self::T09_30, self::T09_45, self::T10_00, self::T10_15,
            self::T10_30, self::T10_45, self::T11_00, self::T11_15, self::T11_30, self::T11_45,
            self::T12_00, self::T12_15, self::T12_30, self::T12_45, self::T13_00, self::T13_15,
            self::T13_30, self::T13_45, self::T14_00, self::T14_15, self::T14_30, self::T14_45,
            self::T15_00, self::T15_15, self::T15_30, self::T15_45, self::T16_00, self::T16_15,
            self::T16_30, self::T16_45, self::T17_00, self::T17_15, self::T17_30, self::T17_45,
            self::T18_00, self::T18_15, self::T18_30, self::T18_45, self::T19_00, self::T19_15,
            self::T19_30, self::T19_45, self::T20_00, self::T20_15, self::T20_30, self::T20_45,
            self::T21_00, self::T21_15, self::T21_30, self::T21_45, self::T22_00, self::T22_15,
            self::T22_30, self::T22_45, self::T23_00, self::T23_15, self::T23_30, self::T23_45,
        ];
    }
}
