-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 05, 2025 at 02:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `store`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `country` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `postal_code` varchar(255) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `location_accuracy` enum('exact','approximate','general') DEFAULT 'approximate',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `title`, `address1`, `address2`, `country`, `city`, `postal_code`, `latitude`, `longitude`, `location_accuracy`, `created_at`, `deleted_at`) VALUES
(20, 14, 'Parents', '654 شارع الشهيد ابراهيم احمد', '', 'Egypt', 'القاهرة', '11865', 30.06446254, 31.46122057, 'exact', '2025-09-04 15:56:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `total`, `created_at`, `updated_at`) VALUES
(12, 14, 1000.00, '2025-09-03 21:32:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_sku_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_item`
--

INSERT INTO `cart_item` (`id`, `cart_id`, `product_id`, `product_sku_id`, `quantity`, `created_at`, `updated_at`) VALUES
(4, 12, 1, 66, 1, '2025-09-03 21:32:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart_promocodes`
--

CREATE TABLE `cart_promocodes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` int(11) NOT NULL,
  `promocode_id` int(20) UNSIGNED NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_promocodes`
--

INSERT INTO `cart_promocodes` (`id`, `cart_id`, `promocode_id`, `applied_at`) VALUES
(23, 12, 1, '2025-09-05 12:28:00');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `deleted_at`) VALUES
(1, '0Qk2smhi0A0ELPl0rhF7dRIhQbccxZvaBXhcbiPLnu8sIh1tWCq2IN4uxt1eGEnR2wmx28KmaCHxHmDVzPKgHvKp7hwLrpTBMzu24QPiFOYpkjmDkz4zy5eWUs8HKfESZv0oXheTaUZWi2UccztTcBqN8kFGQ5OP7OWiXS4r43bERjKyMRZiiSF1', 'This is a description 0595456', '2009-08-06 00:53:00', NULL),
(2, '73ryrjhoanQLOuc65Ngymr1TsKrt1iZ6j6RkT7ARg6nwdoE2axSocnuoOGlS3PNTHJNwFF6No0dMWuZvWtpQAJqTPNA7hQohZS1o030ZrKnSK6bfna6bYDlikhT5tWVBZeUu87LgnEu3QZ8jRSkt8QS86zgWPQcJLy2oQD2ZMmiPrPYj0sYzn6WIsJHG7xLbYZpithIfxp1SeHx4RUaSGPgGCQl8EufcHBFHxVBqyDQh2kf5tu3aslgKM2h6', 'This is a description 8595881', '2014-10-16 22:44:00', NULL),
(3, '3tVl2isO34HGRZKzvXDBPvnRW5g5hOdWfiSon02tQzMPfy53stZRT0JsnJ4RKeBhqIjF6lsYtxU5SV3ySURQ7ZN5b2y4TkdUz3xprhBiMEeSMqjPwWd6fmZCUTIBgM5PbZR', 'This is a description 1232472', '2017-08-28 00:30:00', '2001-05-04 22:21:00'),
(4, '2GeLPqU5lDFEbPIzEvPICH3RQx4YGAAMDlUSPcbWhKduLLk1vJueQ1gzm', 'This is a description 1239704', '2006-12-17 05:30:00', '2007-06-04 21:36:00'),
(5, '1loZkMlLevR3jglIcrq3ZrcwdlYg6HwfhawOFMI7SVlNERnT2G10oJyAeMdAm', 'Description number 3302280', '2006-02-04 05:29:00', '2020-07-13 04:13:00'),
(6, 'AIisIjDpTSBxgbSYVQ3mRUq5ZZJcodR3GLX5dV2xJDSxQbIDP48nNO1VPDJ0bM2iv76pVuuMyQQkoA4Yz6FvNyNklGi5TnMMeKqj4jVbSPkjrQqsWAR', 'This is a description 1160770', '2021-03-30 22:55:00', '2022-01-19 23:22:00'),
(7, 'oE40I3xnPsJlu6T7c3lvXa7nCjjGO6ivKiFEPmViDE8wu2kcFgCx4HUIzhqApKVNaljtezWIdewHYpxfrkawO7uhrtrrTJf7r8FjBm8RoniRa', 'This is a description 0909709', '2021-04-06 04:10:00', '2020-02-09 22:31:00'),
(8, 'W5z8QoidNYXHqbDSve0bqlHjW8C7XKfQdGyMdCqoxdqkU3Ro5wkDNtrKOmTpVxegrDyMYViAKeM7lLREv45R', 'Description number 2226261', '2001-05-11 21:54:00', NULL),
(9, 'KssBhYKwQtIR4Oly85IFzBk0BHKUZYjozyeNGyKWwiGlkLtAbn20pygVUAYhTehmXeTknUvpMh3OXcW6iAs8taOcIrLTwRtQc5BK6H0xGXZ1wp8xMsETjOSYL1xYE5f3AF', 'This is a description 0711305', '2015-02-03 23:38:00', '2023-12-17 02:46:00'),
(10, 'IWGn1QQNRfaygOgciUwBCh1SE82tWTfeznGETl6X8vqad8LQxSGUZ3rroCUKP1a4XMLE4ePHdqfNKbOzzzRrNLhkRSVmUQNZYlIjnRhC2miwbg7HcTVhcZnvJ0c0ZPdVw', 'This is a description 5283341', '2010-06-22 02:12:00', NULL),
(11, 'PdSIdN2DzYtVJofaAkXvFpNRR3gMyabm5AF02BIGadw46lCKvDuw0E5VAXyWZaH8g7lDWcJcEfTepHLehHwauGEGS54INQoWyVKBIerVJR52wg0ItJXjY8DO46toxmVl4FGMbz0nKItjcc5cnygk2Solz4rkv3DMwhKZCPXNFDZ7eBHnJmrLUPu40SSwyoPyERrqteM8eOtNwZLxdcOAbMiK5WHEZKI0dcFFgMJtHMxFxLJSwLVm3gfh', 'This is a description 4466637', '2020-07-24 07:01:00', NULL),
(12, 'ILOwkuHaErUWfdR2vtvpZggXtnCZH2YdiGYMSY5FNy1qO2susITQB6cO5MeDHpygVkuJTMfSEG8OUfMgrTN', 'This is a description 0314253', '2021-02-12 03:16:00', '2010-10-18 04:21:00'),
(13, 'R2dj8J71oS761YoytDSUCr4jVHE7yIHJFc4kMpUOoCzapW5fwR4xDoH4ndjjEOojW22GxU2t6JjcdePM16sV3ipNmmjgTVcGox8hQ6IEMB0wAW5Hoq7eps5g8D4Eug8Y8f6qVyFLQBmbi460jcFcD6azVQ8dmNV4s2lq', 'Description number 0868662', '2017-12-02 05:39:00', '2015-05-01 01:07:00'),
(14, '3GPxXPPx0EQTQSRrKPSyO6pHVEc', 'Description number 8855841', '2014-12-29 02:37:00', NULL),
(15, 'Y0I1cY7yA7twDR1TjYU00QJovAVIZ48RSSOA3Oo3kHCpxwgh3FIveYMnnHpMo6Y6OnH8oSouJIRDSxtkZxSSobdiGo1nOKFbeeLVF6c5oFsV23S0pJfxXUpT1rhjfPjHPEzkAVD8lSr5FP6tz7UBuvR6ia1OnnjUYXLdKfhtoZTmRAdLfSQB8oRdVMWcWzWmwo41xa6', 'This is a description 9915064', '2015-11-24 02:42:00', '2002-10-13 21:50:00'),
(16, 'XB4PUt6SycO54E6JBqAPTWaeykiXdGAY4wdn3YZppfu0GOMgRgwDu4DwTpx0JlDT7GTPhqiG4eEVbOU58SGRiWmaZWYdEgiWymOLiq24ZnsxguYhXyL8daGMoMnxUUGpmwIWV06WEYHESp5LgJV7XtAaPOAhsIRIax8klGnQTdhGf6Sg0P0s75S5WEFcq7nBOgVJTmmCLBoN8p6Xy4jxBmhGG44OBN6lVZjp62cyOkQzoHwUmqC', 'This is a description 9915697', '2015-07-11 23:41:00', NULL),
(17, 'oeqKJH5pu7pVI7zsOt7MoYztkueSuTC1THCcyOlTrtXy0Kx3o1edopoESTEf36e0BmprkESwex8T6P7FWqa6kByMJtMFZpda7RX7sVc7L4ujRSCwB6p2XTDRn8cOlRPshye80HTsGTD7JrlJ5pIS66CjE2n64IWckba3T6OtkimUTzon48GUbPrGUVJoAuafoTrlmoPD6cTaEt18kBI3JURHihsC5aybhRWAyEtm0', 'This is a description 2471038', '2015-09-22 06:04:00', '2021-06-19 07:39:00'),
(18, '4IOQbVeo0iPowdllSQm8TMdhnRSEy1FcV6R3oxJz8j1VNk1EKUlkBdCYu', 'Description number 4773883', '2002-07-22 04:02:00', '2017-09-16 02:10:00'),
(19, 'DCm1eoB8xuHJc2YSWz7EpiD6MuZEZOGyD02VSowpuAtVeXMvUQEyVZgJypdSVzcS5zlz5OXcdEfZ2OOhuVNQe85Zawdtn1xdYKJYLNhArCLFOmW88xs5xq2No2d7JNtwU1KFJxuM1nnds5pKjxhfarGFNMBfcn4HWwKsOFxwSeo3Qng8knWyeWfNMKLxIcujqFFBtcxqqg83YPy3GWVAmI27Ug0P6wZkdSub8p2S', 'Description number 3101809', '2021-09-18 23:45:00', '2005-08-27 01:37:00'),
(20, 'Nv2KkyzquCdDIAe7Elbeh4gO155xji7u11QqCOs6dyNC77e1lxoDqNSiwb5Y1enmxDsqR1gLtqLZFxVXMoxIdZFm03cPkG666lNyEaD8OThpictl2n0oBfAY8zE2H0fTwo6ww6oFYHpDKIC1KnRoqaxG8W7Vel3Iz5wrZehCXGOHIg6Auq1CjNvkRh6UyW2jpim1YIwLsKqBWACdvGSLxLMruX1xkGaOcGbFm7s5HunTrSPgcviud', 'Description number 5840539', '2002-05-17 21:52:00', NULL),
(21, '0uiaogeuXyBMbLXYpa8FklcBqJz4GOT0ErBlRPDqJJy6x3Z7KWpCsVBHzl2kqVS1sl4WFHgYB1opPFf5GTBH1B8eQIsBpef7sSZ4Ppv1X61X8lanyTlwtVz2D322RdwI3ushgH2xwtlXqZYKz8SHRgSowG0F4KFEtdNpSteeVpA0YErL5BmKMHtKE4y12AIrFJ1OJpRutqrT7IWZBp7n5VkWIHHucQiy2GsPT0gSAgoCzSlYtYdg', 'This is a description 2216695', '2007-07-07 05:25:00', '2019-06-02 23:32:00'),
(22, '2lQTe2thuLG2x4zO7Ygutxj0Frdqnz1wfEGfuayPP8OggFkmvGomVRem8lVJAlX0DBJowNFFnXZnqbkcIvxN5V4eDKEUKWr65kIsBBM8C1JDYa274KGxwZLXo7jJNrLpLDvq8FKDpRIQPuw1LSuJymsu', 'This is a description 1840512', '2015-09-22 00:26:00', '2000-11-24 00:03:00'),
(23, 'rmqI4MLxJW38cZDTOvpeMoh6h4tprmPV0zOTh6Mb3tnBIcYEmi0ap4l26zrxBHgcMdNigcVJlytWIepxBnNl23zocEZnrzJR6RjBikoNq2sshfCj26qeE6s0UBDGOuhKyGV27VIq', 'Description number 4978536', '2001-07-19 06:32:00', '2001-04-13 07:52:00'),
(24, 'vs3XdzHGOCMshgbELpj82tLIAqIVjSkyBqhUhuAcR67wAQR1DlMifEikX5WBDBTyWzUpdCSpr0e2CLypTKM7tXnAeBw4cGK4ogG5risfb0jNTv5MOnZLJtTMGGVVpR2p7soQBtpFxsrS6gGqxLDCcIJcDcTCfpNZv5pcEOXEUN3J0LCtUxhJzRFnJP54kohs4PYSyGDJbGRjm2Jwfh', 'Description number 2814238', '2019-07-14 22:02:00', '2017-12-05 04:30:00'),
(25, 'XlebPdbjLdGB1EciO8wIpAN01GTTK6nkPsQTptwerWQDIRtLN0FNlZzn71uAb4gl2fhxjou6ZtcZxLcDR3tH7bN4zhosrqz20d7ron3xBBTQaCIWss35JyGCyUUeLpCYt', 'Description number 8615200', '2011-07-03 06:13:00', NULL),
(26, '5HTlpaD3x2UstTKJTyLrV6WSxMaR6ZhMac8faAeBw6GwvQlqYbYCKLxtf2duqMUoHyheKtXS8VGgPRGp0WJvJFhwgIRMkszLGX4227o574P2hmq1GMUiGgyKMNnG5vrHKaEZ5QryOvLcDsY8EiysHGd4csGMCdauVyenhg20zUDzI8nQdZsY60Yq6Uf8sKodkfAYv2IGAzpVYa0aasn4NQQ4ChjrkiWh5fMY3ARRLy', 'This is a description 1631208', '2022-03-25 23:00:00', NULL),
(27, 'pklZMPS3ZkKLkA11AGyBsU2eebhRSl31Ywl4YrJZcpdXOc8ioFPxCjhTti2F6GXBrTqN6nrcIE5yZKIQbd7lhFDvf8tfoQqg67GyfpgtMqJ5MaNGYY4Qq5Q2HvJgjDEL08M6U14SGzrHSZLOi8ErDYuZSEI1aLILMvEvzo0tnKEME3HXjl1p4Gs7odfTnXkIWaZ0hI25bh', 'This is a description 0577744', '2007-02-07 07:21:00', NULL),
(28, 'liIajdQHLhDpH1i3VgoDLEtZSk5FotGpfYFnzER6F3YysN0f5A8z8y4WlOVHscsFfWIXEBJdB28kHVM', 'Description number 0078035', '2014-08-06 02:14:00', '2021-09-28 07:09:00'),
(29, 'PVY8XxCsfN4u7z8RoyF8aRWoo5BSfdWT33RoWFmN', 'Description number 2416596', '2007-06-20 06:37:00', '2022-12-07 01:38:00'),
(30, 'wYVLT5aNTvecoXIJHUsNzNQjufTERzx12TksCYDSDRaXr4Ky8AGTAxUZjt5H28jFOhiz4XW6woHZUTv2AqkYEkCz455rg54CZPvLCvNdjNLDyfwomKCCbvmPJay7zFDhru1p4TOAEk0bvNjdlQFcRXg4SvzEaDXbisvWS07hiR3PhyN2yHkQFaDJam2fwnN5R5cGBGwffHrRMCQbIK1cQNAuzQbUlRxeDkd5aSsbsmV6b', 'Description number 9245451', '2022-01-05 02:38:00', '2004-07-21 00:38:00'),
(31, '1uPu8OO3R5I', 'This is a description 6243087', '2012-08-18 04:16:00', '2009-01-30 23:15:00'),
(32, 'ioreGYNWK4HnOQckrFKG0yiJJ1fYulcFfh31DRdSntRzX4xAtCAp4aUFMA08RcC5HdzBRGYU', 'This is a description 5171934', '2000-07-28 07:29:00', '2007-03-21 22:38:00'),
(33, 'YDAjZJVLmbpOMu1C2QDnaGIj4M8zkNejNCBuFfHSzWi7Q6toA', 'This is a description 4329731', '2001-11-02 04:28:00', NULL),
(34, 'kQIjP', 'This is a description 0672995', '2004-10-17 01:32:00', '2003-04-15 04:26:00'),
(35, 'MtfwqW25h5rFawwuTCmbhW42lT0j3Xy4U6rga05r3jy5mlhkJlbkhgqREGd6ZFqq81GTcu3uu0nTlO0qGMGlGGLeFQuCVkCbiotXDnoLUimlphPJNf6B3M8uVEE6YXQtxHanzhFB4U5lyHgWu4MJLhnn2OTGitiXU6XjwUeQwHsn36Nt26MkrSpN6P1qA7a0seUGAmD0PsZmhrRup4lxse', 'This is a description 5946061', '2001-05-02 21:30:00', '2008-12-21 23:24:00'),
(36, 'AsTLWZc8YyA6KN8of132hYuVCRocHhokoQVAp2E5FdriohwCQmISAnMEyCpn3VLIu2eupf8yLMDApOpIRSSc5b47florMj6UHd5Riq3cKOSVaJWG8c6tjncjlFlp77Djn8302zXfQQAEgXqE5RpRXyH2vBtBQnS2sBaoW0ACA3bGeo4cKCYdJX54xjsU6jXOmsJr2y6F07y', 'Description number 9338001', '2002-07-02 03:29:00', NULL),
(37, 'Si8W7PXSfdEo4tHquY1szvd17qZ7eFidDKItujcRj7ANr0R0e6TPuJGwAnpHUmf2ZZzmurHdySVZRbU0VWrHNRbemJPZzNBjLeFw1EFyLpLyBAwLIKm0GIKYADqeq2hyFJRPvrIm8Yf0arwJLCaHNZv5uZAyZsBUVaWqURwEQngHf0EQeqW3YqEVSWAaaMFgCfVQcYpbnv04iLgeQyyU8nYRA3QgRDVzm7bsV5bxqXxDU3eu', 'This is a description 0574943', '2008-09-07 00:11:00', '2019-09-23 23:16:00'),
(38, '2GPmyVeAHhUFFs5Bd8TBKpqH7xHamSo6DF6HkTThhdfoKfqmTFbrPRjRtF6nCxhDy6pi8ZcBRCYYbpyxDkpcWJWp2XD30WS5jKaKbsmuiUKp5ljU4cl1DpGoscdeBJkfjiQcbcSLnDw25lpdJxGXpwaDdPrSyoqeaEL3HAr7oMYwdsyZjVEu0zvYZhE2baauruhdLr5V', 'Description number 4128297', '2008-07-03 21:22:00', '2015-04-12 06:21:00'),
(39, 'GIXneTOqZCD3ImM1iyEwzMbj5cpRe4U2slWejoGwZUDHvplQZZLIU74rBfhM6vdmQBUm1D15RqAd0ZayPEFIyHVsBNrpGpWgsN4rqG7nWHklnBqvC3Kmb2dYSlGVY5q2ExgL08u660GQNIgkXquV1HK0', 'Description number 6383053', '2015-09-04 03:44:00', '2019-01-20 06:10:00'),
(40, 'UCazsrPE8ox7ePQdJpWtyZygR16UOyCbQ0qvqGAWsTnPBNYkzVg5Bc8AHc4Er4uJ4KMVWXs87qJcFVfxJ3al04YtE3vb2Csy52JQ8DuTNev2wVvgZtQ6AomQrHmSauGP4EmOc0GSjw5Vf43D4nuoFqpFZ3UEVsz1MoV2Pv0oNEcPfo4MfDoO21C2N1AdnienpaRS3nciw64LBdF1utbb', 'This is a description 3955980', '2022-04-12 02:50:00', '2009-10-07 21:20:00'),
(41, 'WabGUKWWPOSd1IukxDry6prxTs1sbK1DFMBVKyMwpPWuLJoVmmpYeA0yrPvHnonffBJWnSEJ0pQAwWKya5AvcSVVyiKBlbiWC653Ecizh16D6Btn71gGcWWbF8ewHx2c5jNQTdXQb6cFwtpus8lbkaPRk1xfngsacB8g14mhMoNY71ENdovWaJuQuuYZSAubDKTQZyxXKVQPNs', 'This is a description 3424777', '2008-12-05 22:17:00', '2012-04-22 05:18:00'),
(42, 'Va8tMOSBsP1jgJvx7yS2IBfHURvAICPILRkiErFJ6VFC8tcEGjw', 'Description number 9425665', '2003-07-06 07:28:00', '2006-04-14 02:05:00'),
(43, 'IT27FP22CLeeBLk6Jeizl5glmVMPnH4jqEw5pXVWILtUMWkwndEkCdlb3IVZf7UHznX113PYtSetT05AmlCi3jLPfstkkZf2jLabCrkPZLYwENOntRPNILQJwTUL4f', 'Description number 6277318', '2001-07-25 22:11:00', '2007-11-30 00:23:00'),
(44, 'MF0BEEuF5zvJyrkBE6QLP0Y8aThKa8Fr8icniJkgeyDJt600JIJRgpVXzODrfCoa4r0w1dRfTMUVMPi7lMoV5DPr3an3kKRC6EYNQBJ46oCVtGXwDZz0L0FpkYnBat3R3KMFJvqiqk0PoUtCmToO1W1XVIlTcFfZ8cD3LJI5snusi4332INmVwd5J8AM8ZM44jwJ', 'Description number 6580377', '2018-02-21 07:12:00', '2016-05-16 07:35:00'),
(45, 'dxDMqsLlMORLOgv23JSZKaREwXv1VDlzTMID8lW8cSMn', 'Description number 2322864', '2010-04-13 22:11:00', NULL),
(46, 'JV6xSKUzTf5ieTysHCS3JYrYDPy3M1Econ1wK4sX1JTEbB28glkHh2iD3ejRy4vk4ttHFsvYxiBXMgdevWdg', 'This is a description 6732768', '2010-03-07 02:28:00', '2009-09-30 23:41:00'),
(47, 'I4z2udRQ7Xcji', 'This is a description 4655508', '2013-02-10 08:37:00', NULL),
(48, 'uJzFna3kCIWSF4HPIknJsz5x6s4xtfW5FBeW0Kq7cDevP2fwbmOpznj5PWJjd17veSobreAIfsIXFX5TFYLHQ4TWjUtbM7EPl5qghvydDRWfz23rCGbHuKryRAEUAaJnVUVD0DTHZpUYLBpCPCBEth6FOxU2', 'Description number 3472049', '2016-02-04 02:25:00', '2018-06-29 06:41:00'),
(49, 'lMrufBJTXNs5MHFQ3kUOHiYrfKHfYoKnUL4IUlkSgGDTGvqFhLAEnLFxcdSCcDs2gkwIschY8MlLf4mQf8BAP6LjZcRW0yjHMkFGWkFl6M4ulrL8aPaKzjcRwynDbwthyR5xCawG5PTmkGMAV7bn2VOfHNLw2fZopPXVLKR0FQjBhMDAfA8eHb5junyJqDm8AgZyqbAXFfZX03', 'This is a description 4982825', '2010-04-01 04:00:00', NULL),
(50, 'njIveloTzRXWhLeWv3U0qa7sSpmxCNJJ2RIVOAGacX0mfgC75kOhmAfbELwvIVZLGj3AxOdatPFBcSnrUroVltzCX85HyqIfYNO3C3pCKAjXjQK5jgwUM5NE1t1yImV8bnIfcuKbf7RcrYVURVw5sluJ1JtvRhTUEf5Wnb4PknOTkqyzHLwMQwvBTwmi3w84iI62hLFrjymw5jvYArqTDKDvgXz1zE535HNdd3PFfZUFR6H3sNwUVVXqja', 'Description number 0663878', '2005-10-24 07:27:00', '2020-07-01 07:36:00'),
(101, 'Audio & Headphones', '', '2025-03-24 10:00:31', NULL),
(102, 'Smartphones & Accessories', '', '2025-03-24 10:00:31', NULL),
(103, 'Laptops & Computers', '', '2025-03-24 10:00:31', NULL),
(104, 'Wearables', '', '2025-03-24 10:00:31', NULL),
(105, 'Speakers & Sound Systems', '', '2025-03-24 10:00:31', NULL),
(106, 'Televisions & Home Theater', '', '2025-03-24 10:00:31', NULL),
(107, 'Keyboards & Accessories', '', '2025-03-24 10:00:31', NULL),
(108, 'Office Furniture', '', '2025-03-24 10:00:31', NULL),
(109, 'Home Security', '', '2025-03-24 10:00:31', NULL),
(110, 'Personal Care & Hygiene', '', '2025-03-24 10:00:31', NULL),
(111, 'Charging & Power Solutions', '', '2025-03-24 10:00:31', NULL),
(112, 'Fitness & Health', '', '2025-03-24 10:00:31', NULL),
(113, 'Kitchen Appliances', '', '2025-03-24 10:00:31', NULL),
(114, 'Smart Home Devices', '', '2025-03-24 10:00:31', NULL),
(115, 'Wireless Accessories', '', '2025-03-24 10:00:31', NULL),
(116, 'Storage & Hard Drives', '', '2025-03-24 10:00:31', NULL),
(117, 'Home Automation', '', '2025-03-24 10:00:31', NULL),
(118, 'Cameras & Photography', '', '2025-03-24 10:00:31', NULL),
(119, 'Computer Accessories', '', '2025-03-24 10:00:31', NULL),
(120, 'E-Readers & Tablets', '', '2025-03-24 10:00:31', NULL),
(121, 'Lighting & Lamps', '', '2025-03-24 10:00:31', NULL),
(122, 'Small Home Appliances', '', '2025-03-24 10:00:31', NULL),
(123, 'Portable Power Solutions', '', '2025-03-24 10:00:31', NULL),
(124, 'Car Electronics', '', '2025-03-24 10:00:31', NULL),
(125, 'Drones & Aerial Photography', '', '2025-03-24 10:00:31', NULL),
(126, 'Projectors & Displays', '', '2025-03-24 10:00:31', NULL),
(127, 'Action & Sports Cameras', '', '2025-03-24 10:00:31', NULL),
(128, 'Digital Art & Design', '', '2025-03-24 10:00:31', NULL),
(129, 'Gaming Consoles & Accessories', '', '2025-03-24 10:00:31', NULL),
(130, 'Virtual Reality', '', '2025-03-24 10:00:31', NULL),
(131, 'Smart Lighting', '', '2025-03-24 10:00:31', NULL),
(132, 'Keyboards & Input Devices', '', '2025-03-24 10:00:31', NULL),
(133, 'Streaming & Video', '', '2025-03-24 10:00:31', NULL),
(134, 'Smart Doorbells & Security', '', '2025-03-24 10:00:31', NULL),
(135, 'Vacuum & Cleaning Devices', '', '2025-03-24 10:00:31', NULL),
(136, 'Laptop Cooling & Stands', '', '2025-03-24 10:00:31', NULL),
(137, 'Personal Grooming', '', '2025-03-24 10:00:31', NULL),
(138, 'Home Security & Surveillance', '', '2025-03-24 10:00:31', NULL),
(139, 'Air Quality & Purifiers', '', '2025-03-24 10:00:31', NULL),
(140, 'Solar & Renewable Energy', '', '2025-03-24 10:00:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_ID` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `discount_type` varchar(20) NOT NULL CHECK (`discount_type` in ('percentage','fixed')),
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `product_id`, `discount_type`, `discount_value`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(2, 1, 'percentage', 40.00, '2025-09-01', NULL, 0, '2025-09-04 14:50:33');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_transactions`
--

CREATE TABLE `loyalty_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `action_type` enum('earn','redeem') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('read','unread') NOT NULL DEFAULT 'unread',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `user_id`, `status`, `title`, `content`, `created_at`) VALUES
(1, 14, 'read', 'discount', '40% discount on sound core r50i', '2025-09-04 18:58:27');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `user_id`, `cart_id`, `total`, `created_at`, `updated_at`, `status`, `tax_amount`, `tax_rate`) VALUES
(124, 14, 12, 1200.00, '2025-09-05 12:28:51', NULL, 'shiped', 100.00, 14.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_sku_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `upated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`id`, `order_id`, `product_id`, `product_sku_id`, `quantity`, `created_at`, `upated_at`) VALUES
(35, 124, 1, 66, 1, '2025-09-05 12:29:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `used`, `used_at`, `created_at`) VALUES
(13, 14, '915ddf1a29b90fcb9679b1e98e90b5b905925b58fbf60fcded6aa47c27d85e1e', '2025-09-04 20:00:10', 1, '2025-09-04 19:07:19', '2025-09-04 19:00:10'),
(14, 14, '3473c8b03a023dec84dfb2e2c4250b28118301ea0fb7ab1955da6a3694600ce3', '2025-09-04 20:11:36', 1, '2025-09-04 19:12:16', '2025-09-04 19:11:36'),
(16, 14, 'db2223bccb1b82986f956e9c759fd585ed176c2550fcce7e7a6f7e48c625f059', '2025-09-05 00:31:59', 1, '2025-09-04 23:34:39', '2025-09-04 23:31:59'),
(17, 14, '253f3c9b28ee53f3a3d6218e3c66599d776b908eed232291a0acc57a26167a96', '2025-09-05 00:46:30', 1, '2025-09-04 23:47:29', '2025-09-04 23:46:30');

-- --------------------------------------------------------

--
-- Table structure for table `payment_details`
--

CREATE TABLE `payment_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `provider` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `function_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `summary` varchar(255) NOT NULL,
  `cover` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `summary`, `cover`, `category_id`, `created_at`, `deleted_at`) VALUES
(1, 'SoundCore R50I', 'High-quality noise-canceling wireless headphones.', 'Experience immersive sound with active noise cancellation. hello hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  hello  h', 'img/product/r50i/13.jpg', 1, '2025-03-24 10:31:11', NULL),
(2, 'In-Ear Earbuds', 'Wireless and wired in-ear earbuds with deep bass.', 'Comfortable fit with superior sound.', 'img/product/earbuds.jpg', 1, '2025-03-24 10:31:11', NULL),
(3, 'Flagship Smartphone', 'High-end smartphone with powerful processor.', 'Premium design with cutting-edge technology.', 'img/product/flagship-phone.jpg', 3, '2025-03-24 10:31:11', NULL),
(4, 'Mid-Range Smartphone', 'Affordable smartphone with great performance.', 'Smooth experience with long battery life.', 'img/product/midrange-phone.jpg', 4, '2025-03-24 10:31:11', NULL),
(5, 'Gaming Laptop', 'High-performance gaming laptop with RGB keyboard.', 'Perfect for gaming and creative work.', 'img/product/gaming-laptop.jpg', 5, '2025-03-24 10:31:11', NULL),
(6, 'Ultrabook Laptop', 'Slim and lightweight laptop for productivity.', 'Powerful and stylish laptop for professionals.', 'img/product/ultrabook.jpg', 6, '2025-03-24 10:31:11', NULL),
(7, 'Fitness Smartwatch', 'Advanced smartwatch with fitness tracking.', 'Monitor your heart rate and track workouts.', 'img/product/fitness-watch.jpg', 7, '2025-03-24 10:31:11', NULL),
(8, 'Luxury Smartwatch', 'Premium smartwatch with elegant design.', 'Blends fashion with smart technology.', 'img/product/luxury-watch.jpg', 8, '2025-03-24 10:31:11', NULL),
(9, 'Portable Bluetooth Speaker', 'Compact speaker with powerful sound.', 'Enjoy music anywhere with wireless connectivity.', '/img/product/bluetooth-speaker.jpg', 9, '2025-03-24 10:31:11', NULL),
(10, 'Home Theater System', 'Immersive audio system for home entertainment.', 'Crystal-clear surround sound.', '/img/product/home-theater.jpg', 10, '2025-03-24 10:31:11', NULL),
(11, '4K Smart TV', 'Ultra HD television with smart features.', 'Cinematic experience with vivid colors.', '/img/product/4k-tv.jpg', 11, '2025-03-24 10:31:11', NULL),
(12, 'OLED TV', 'Premium OLED TV with deep blacks and vibrant colors.', 'Best picture quality for entertainment lovers.', '/img/product/oled-tv.jpg', 12, '2025-03-24 10:31:11', NULL),
(13, 'Mechanical Keyboard', 'Gaming keyboard with mechanical switches.', 'Responsive keys for better typing and gaming.', '/img/product/mechanical-keyboard.jpg', 13, '2025-03-24 10:31:11', NULL),
(14, 'Wireless Keyboard', 'Bluetooth keyboard for convenience.', 'Works seamlessly across multiple devices.', '/img/product/wireless-keyboard.jpg', 14, '2025-03-24 10:31:11', NULL),
(15, 'Ergonomic Chair', 'Comfortable chair for office and gaming.', 'Provides lumbar support for long hours.', '/img/product/ergonomic-chair.jpg', 15, '2025-03-24 10:31:11', NULL),
(16, 'Standing Desk', 'Adjustable height desk for healthy work habits.', 'Improve posture with an ergonomic desk.', '/img/product/standing-desk.jpg', 16, '2025-03-24 10:31:11', NULL),
(17, 'Security Camera', 'Surveillance camera for home and business.', 'Keep your space secure with real-time monitoring.', '/img/product/security-camera.jpg', 17, '2025-03-24 10:31:11', NULL),
(18, 'Smart Door Lock', 'Keyless entry with fingerprint scanner.', 'Enhance home security with smart locking system.', '/img/product/smart-lock.jpg', 18, '2025-03-24 10:31:11', NULL),
(19, 'Electric Toothbrush', 'Rechargeable toothbrush for deep cleaning.', 'Advanced technology for healthier teeth.', '/img/product/electric-toothbrush.jpg', 19, '2025-03-24 10:31:11', NULL),
(20, 'Hair Dryer', 'Powerful hair dryer with multiple heat settings.', 'Quick and efficient styling tool.', '/img/product/hair-dryer.jpg', 20, '2025-03-24 10:31:11', NULL),
(21, 'Wireless Charger', 'Fast wireless charging for smartphones.', 'Convenient charging without cables.', '/img/product/wireless-charger.jpg', 21, '2025-03-24 10:31:11', NULL),
(22, 'Power Bank', 'Portable battery for charging on the go.', 'High-capacity power solution for travelers.', '/img/product/power-bank.jpg', 22, '2025-03-24 10:31:11', NULL),
(23, 'Smart Scale', 'Body composition scale with Bluetooth.', 'Track weight, BMI, and more via an app.', '/img/product/smart-scale.jpg', 23, '2025-03-24 10:31:11', NULL),
(24, 'Fitness Tracker', 'Wearable fitness device with step counting.', 'Monitor daily activities and workouts.', '/img/product/fitness-tracker.jpg', 24, '2025-03-24 10:31:11', NULL),
(25, 'Blender & Mixer', 'Powerful kitchen appliance for smoothies.', 'Make delicious and healthy drinks easily.', '/img/product/blender.jpg', 25, '2025-03-24 10:31:11', NULL),
(26, 'Smart Light Bulb', 'Voice-controlled smart lighting.', 'Adjust brightness and colors via an app.', '/img/product/smart-light.jpg', 12, '2025-03-24 10:31:11', NULL),
(27, 'Noise Cancelling Earbuds', 'Premium earbuds with ANC.', 'Block outside noise for immersive listening.', '/img/product/noise-cancelling-earbuds.jpg', 2, '2025-03-24 10:31:11', NULL),
(28, 'Smartphone Stand', 'Adjustable phone stand for hands-free use.', 'Perfect for watching videos and video calls.', '/img/product/phone-stand.jpg', 3, '2025-03-24 10:31:11', NULL),
(29, 'Gaming Mouse', 'High-precision mouse for gaming.', 'Adjustable DPI and customizable RGB.', '/img/product/gaming-mouse.jpg', 13, '2025-03-24 10:31:11', NULL),
(30, 'Streaming Webcam', '1080p webcam for video calls and streaming.', 'Crystal-clear video and built-in mic.', '/img/product/webcam.jpg', 14, '2025-03-24 10:31:11', NULL),
(31, 'VR Headset', 'Immersive virtual reality headset.', 'Step into the world of VR gaming.', '/img/product/vr-headset.jpg', 5, '2025-03-24 10:31:11', NULL),
(32, 'Smart Coffee Maker', 'WiFi-enabled coffee machine.', 'Brew coffee remotely from your smartphone.', '/img/product/coffee-maker.jpg', 25, '2025-03-24 10:31:11', NULL),
(33, 'Air Purifier', 'Smart air purifier with HEPA filter.', 'Improves indoor air quality and reduces allergens.', '/img/product/air-purifier.jpg', 6, '2025-03-24 10:31:11', NULL),
(34, 'Dash Cam', 'Car camera for recording road trips.', 'Provides security and evidence in case of accidents.', '/img/product/dash-cam.jpg', 17, '2025-03-24 10:31:11', NULL),
(35, 'Electric Kettle', 'Fast boiling kettle with temperature control.', 'Perfect for tea, coffee, and more.', '/img/product/electric-kettle.jpg', 25, '2025-03-24 10:31:11', NULL),
(36, 'Car Phone Holder', 'Magnetic phone mount for cars.', 'Keeps your phone secure while driving.', '/img/product/car-phone-holder.jpg', 22, '2025-03-24 10:31:11', NULL),
(37, 'Portable Projector', 'Mini projector for home and travel.', 'Watch movies on a big screen anywhere.', '/img/product/mini-projector.jpg', 11, '2025-03-24 10:31:11', NULL),
(38, 'External Hard Drive', '1TB external storage device.', 'Backup files and carry data anywhere.', '/img/product/hard-drive.jpg', 6, '2025-03-24 10:31:11', NULL),
(39, 'WiFi Router', 'High-speed wireless internet router.', 'Provides stable internet connection.', '/img/product/wifi-router.jpg', 18, '2025-03-24 10:31:11', NULL),
(40, 'Smart Mirror', 'LED smart mirror with Bluetooth.', 'Features touch controls and voice assistant.', '/img/product/smart-mirror.jpg', 24, '2025-03-24 10:31:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_attributes`
--

CREATE TABLE `product_attributes` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_attributes`
--

INSERT INTO `product_attributes` (`id`, `type`, `value`, `created_at`, `deleted_at`) VALUES
(1, 'Size', 'Small', '2025-03-26 08:14:35', NULL),
(2, 'Size', 'Medium', '2025-03-26 08:14:35', NULL),
(3, 'Size', 'Large', '2025-03-26 08:14:35', NULL),
(4, 'Size', 'Extra Large', '2025-03-26 08:14:35', NULL),
(5, 'Color', 'Black', '2025-03-26 08:14:35', NULL),
(6, 'Color', 'White', '2025-03-26 08:14:35', NULL),
(7, 'Color', 'Red', '2025-03-26 08:14:35', NULL),
(8, 'Color', 'Blue', '2025-03-26 08:14:35', NULL),
(9, 'Color', 'Gold', '2025-03-26 08:14:35', NULL),
(10, 'Color', 'Silver', '2025-03-26 08:14:35', NULL),
(11, 'pKkrQzVmKkVk7qsDfbOdksk3vJW3oJFQ2lydfv234OebmhFbGIJ0nS7vv0i7Tb5XFjUB4gJiyCLpliFKdalbEUsMFlOAGpQpXMWP7gJ6WOuutcZZvkc8b51Snk1Ics3MWxQZlP0OShACzyQbTrTmGCTauJcSWeu1LgXwE', 'OxRCNLDMdkVCo2mM4cAeESNJJeWabGSFJ5lXFYZYQ2j2xsjBTaeR2cYRz2l2qWmhOxDnyKmnTvX80ITjq73NOJOaOCXl8Da74Ln', '2005-02-08 04:23:00', '2020-03-09 05:54:00'),
(12, 'JJxeYITo17CaWmeLRqONMwVe7cSkuPf2EgiIFoAEorZJYy6UxRAWzS34fDOo8MDmBBW45040MglxBTgAqEMaBe5zCZMGAAbr2qH6XncHx5N2BNfTVIUdLxYv2VENaox5Z2TXuHVQjYYwi7ZW6ZgtsCcxf1dWaWNCk5BBFVdDCjgdEIdpSOKF2BuOCwDpohZ0cpC2FtY2kEKKXMR6C4todiTEqfeQV6YwS41yeeK', 'KxvKw07CcfStuPjTKQUI7FqXKiV0bnZUkqWomxDtil4khgihyU6FHoJritY74tFYGF3VgF05CE374bNbXv4Gw6TUdIlYRfnM1kTk54F73EFDwRaPHZNbkz2vhXD3OieUv8Xei8mQ5ROuoh3GfmVm0oMlmmz3kcSF6L5cYvsukdlOWU0TsuLsleMHXDvSQFac0muz7kWxoLCTNWNtJWc7zc3Qd8ubm3V4G7r0YGfiGAdLKkG', '2021-09-05 01:18:00', '2013-12-18 23:32:00'),
(13, 'qkzOGvdvLliw620qnjYsI8UyXR', 'BUGV', '2015-06-10 23:30:00', '2015-07-07 00:55:00'),
(14, '46L7', 'qgMkVwcnQKIhSqFRluiwcn2dS1vWN8M6xwsMxpwLQHB4ToIxuvN6xHCKkH4KNv1xYHFLOT6SsCHW7Ezb6ojOOZhIPLolCR0Pe3Y02IUk4UH2QhjIgGA8XU4um1oliCiEGqlaBstBJSr2NHp7G0sWKvxOTiVv0FvrU6ZS4K17bNCPJNdyhhAilqq0nAFSOxVHyvLu0CscLpRAaFtSceAgWOnHEP5Kbww1yTnGbEsjhHUu470R', '2005-02-20 05:00:00', '2016-11-23 08:30:00'),
(15, 'rLsYFfrhlfwMxF8VhPa6hDEJGBqON1EG3nVKdrg1T1V3aQSM8rqTnw1IqJ20wWJnV01NYdHI7JIoVU18P6M2V38MY13odeWCfB0YFElxZkk2rD6QqGSoQHO2Tmk52DEqf2HJekhh6G8cGUK41TKIGcS7bCsPQch0nOHYQX0bBgUQupLLxF7HoAzWJVZtwQ8P8gGm2SGBABVMLPkApsGxM', 'tD6ZtkFlU7Xa3oBgmSHPSeKrv0SuIStDW10gdVoTEzrOoS4Fr4TyLfe3FkFhB1T5kOizo5pHuQMWYylEL1Oc0mUHUdKbiLr2OyIvN1dKnFSCGtleBAEJKc17uW0aItyxRNwfpRAYyVWnHxGhMh313IYSpbLuHesXEfYrqnRgIhrrt2nLPwS', '2012-08-03 21:00:00', NULL),
(16, 'UFTezciJtRarNy12GcMr2FqyWZOJBJmjruRVX0jJVWnDmE0yyVmbM1jGk1tTImKTGHUdiL3J2cQy42eZXBJLj5Z8oX6zNqL8lRwYcejaG55oCeSzGGwpU6qjruDTg5LFRRCsKRCSQcYjjLEKxOselJNCs8mpg1X4xPt6VmtUFiXKY1hb4PQ8PAdCX4FauOUCYUS0UN6EBdsTWAjzpHR5LKkItCI', '63EHkqSpavR5Y3jb0uI7xKRqnXEmRVcDL6HDROHnkj2IQ5E5xCB', '2016-02-18 02:18:00', '2016-08-11 21:21:00'),
(17, 'Ha38pYD3vCTSNlmpEkiReIWgYSSZFAndg1y5gbIMp3Olv5NGeq', 'J7yxOPowAKmEWrk8CWFUEufHVBjelYIAjZURqtiPidGH5ASsmNEJgBAGiZjclEMQCnrd8R1aZYtjGu6ET5IreokwOB7n1kSnQIWKvIexeeYMUOdJv2fTgfNMPHUjpiQJagwyRkOkNs3nmCu5rjcNDEt3B8abk0KHHuLlu8XNibhzshdxcdnJEUjPiWpAksF0jkegTsfWirFnP', '2007-10-03 00:53:00', '2023-09-06 04:28:00'),
(18, 'Z2isUz0qbrGzL3ghHfBbqmraZqfBo1DWAVA5yPWFAQfLFu5oLgihsXQLpvKycc7dLnqRNpOFObWzxn6AHqjME0t1XjxbJM6Y3zXHRPhsRLBivLzFe3SXav13frjgafpj0gFvOfKut0AFFcJYzQ', 'IWN0CXHYArFNIn3Mkljj8UZeMlMnGtisKdSppyD721SNSks5dXLmuA5hoZobJMAtJy2ynR8Een5K7c61VLRCQYIfvGEgEsPQ5IHySHWGxJNSzmhwHZauDtlMM6msqJ0OCZBZO5faAyKvYliyEpxluUW1c11iO7jpOG7CZtiQrpQbJ2LhJejm5J1qu0rUPSCkYwFrzbOMmCoCoekq0jaS218VXFISLjb', '2014-01-29 02:53:00', NULL),
(19, 'UFNekhcDikmWqgR2MHWhdiMOdW1XJW6kO80xHswPSbZ7SZ3uqqC5jT7abPPQs83yV2SxQPuCgqgwSLevFqLhhMnZfRyuEFwQ5zO4wyGdYfaRT6ReYBN7w0PqqaQls5eskeJzd8wXtYNeOkOw6QqaB3aFaySbVAxVJVm4ifLQSCm', '5CjZ0ZDau8rbdwInPEwiSaPTaxEd4gutPOlyKPOOCZl7', '2017-07-19 04:11:00', '2012-12-28 08:36:00'),
(20, 'aOatMIqLk7yYPAugqWapsdkQr4IgGD8FaX8elCf2GQX7yb8llwaJTwLWE3uQPvh8SbgoxzZypOy4RnWoTw6nmwgnY2YaIe7ChAWmqqOOwtFcUqumXGmeV8UfFQm64Hjrapc3TtDnbMDCDWutPCGWAthIidiVDgZpXR2LeyNbp06dL17jC06W1Be0wrGfpImSXSg47r8yylloxkZRYwB3LpQxk5XgIxBkhjJDJb31CW6GPS2Qhw4Hxm67G82QE', '0jACN6HBxMDZ7xoHxyBksD8xuYKWp1pG55IyneRU601ZyflaWK', '2000-08-17 00:50:00', NULL),
(21, 'WNJnibu55oMceXtQIAaX31HrNgnCJ061rjkalONooZlMudnbcH1iYlJWdvj5Fqefvy2ZeFql', 'tchWSMcmM0wGPPc2Bn2WrOAspzeQaJFopUig0o0HGheYkA5fwQUtFshudUKph1NtcqgK2nvMTxbJO32XCSqRU8vmCDpvPv7ExSOyLikbIUrbaIkcaBDGYnyhgNbZQ4FOpBjDRg8Y1bKknEFiFO4DTkUEBUsTGa01oKLOc8BgxPJPoEqQ6rDnE1LHRgwl', '2024-02-13 05:37:00', NULL),
(22, 'dOuexS8y81jV4stFqQVHzpStmUKzvSpRBTS1zhPGCDR4YXypTOtcTREdPCL12QuULUqYROE2FrDXbnsW5eyGs8Z8eP0SgyO1hsou2Z363zri6duumaFWtwTKOHsBgIN4LwfLvBzZzgth', 'OfMwl6OPL6cWmJg7oNjUK4QYPfNapN3ldUD10PX7oTATGhrBTpJq6gdJLlTkqbfksKSunRXgeDMe2A4cAwdIPS8qLnZ8lJMn1riMTegui7a3sv6TLxwOENKc3RhopANnqRZjNqBiMzbWpiKnYOIEPyfbmSWrdMZzSPgIoTyIUA7UwkaW41bJ68zYfynGLwUErOI0jtAEl1pUOpNcgevJLRX5ih4zUHHnelMQoDNHpvBcp6O', '2023-07-29 21:03:00', NULL),
(23, 'jSZGU5rsw5AkTRKTdCMbOmLVCZxz81zsYgO6m6g06drFuhtboyqWLDNDj5zdkeXkoz07YkH1gUian7vv7XiazEUvxcSY840BGtZFmnRlpIVkn5B6qFmxe1ICG8pg7tiJHUarEhnivanrjmJqTmeNlxuTEpa8fqohSR1QGxKstIDabLmCFE3LjNeuvvhtqkOd4ETpLFlg0yE7cam6D7VsL0VwEWzbXTaFqCWZiJS2VTdW5tgOI1kPfRxA', 'WPEugXtsT8N5UHKbKUgaBRrE3Vx8sfrBLSU4yhvXRsdeJuiS7mPfKMhmUPu7tDTT1zmfHAB', '2024-05-14 07:18:00', '2003-11-11 22:23:00'),
(24, 'Ysubc7JSCJk5hC3PkF2eentL4LvZiNbfDSuefhtXVoEjmI4oOGgpyMrp2kzjAstgbnXg2oQAwqtjBAh1Kc0OgPzWTYM6iiTlC7y', 'dpzjgnIDHPypPWriIhuiyUEG3skMeCps4Dpu2763LW7whSdrezQVL7fpGsbqPjo3BSiPi03OibCs7PL7F7dgsqByUd', '2006-09-20 21:30:00', NULL),
(25, '5jjBZP5T0G4gts3zaiv2lQqyP6XsfMDCfT3RCXl3PafKMpf4FLjwe3cn56CuHk5uBRvQHVZ2PiGjFxZj3buW4FQ5axh8VrLyMXJDFSlmyKYqhRSpfkgLsPRJvGEkiJidfHeLnL23DWDaE', 'QkAQioIlAr82nSfMF5PQHudflhwiIkuuavMJmFh2X1HgDNDdsHbetMVXgVk4mw7TowUXM6yt2Rnjb75ItBBxBKEOklCxtk3Ji06uxmloZDx3IjXX', '2014-11-12 08:37:00', NULL),
(26, 'R2rra3pVNgngy3iL3qI6bFsJC6QiRkRG3WK8Or5Ei8pfMX1G0d2qfBbbSTmfp2ExVdQJ7foM3xPIbdp48NBTzMHWcRXvaPZzKlNOgCEBOIrOUKp12s0H3spOsoSATAAEVC6fQKNkIx5E32CvZUyyT3tnvueH6bepXOlSrJzdmCOp8EAXk86U8Z2GfmyaaYZj1W030qjTCcszuc66MIQtIejepYLnSJpdqsqO7Odaz58mvOMvxi2eLVyjHs3', 'PZc0DxFjN6NaMCsFLh64gYswcFmyzFIpdHoHZmtwsvtYsI1q8ZLeSnA2D2YgXTWimSrS5CrrpHtdkhigXEhHuc2Xrm2RJKECqNzuQL02pvphac70Ee', '2006-10-07 01:17:00', NULL),
(27, 'BdD4HlfOKNo5bol4ONL2GnomaA31fbvwkcEcpJbIMffNNqzIw0GFbxoGLo7zZxHgFmN3lz5f1Bx88yhWsspHiHFaZ5IfIJa3nJYFGZxGHFw4KmJcu5kl0hj', 'cFouFTjdHGbGaOCCsWlJKWWzEEp4q0zCNOVy7sLAgezaalhcIFX6SaDLAx2wzh30FZy0xJU1ELamvmR47nQ537SxRjDp6u4sc4yGQb3dQB0Z2c743U5W8HepLgY6TuqBFOnv0ubeBSkAfEqOMAcMoDBPAnLp1MIFmCuvyoZo4DcRh', '2021-07-17 22:36:00', '2008-01-20 23:33:00'),
(28, 'uWYm1KnFW0JTseGXPe6jOS1Af4VlKjbW5041KWDWuC2xRAe4nkJ6rIosb07zKypoHdxxBWLoqIoCD7eDsrWvVXjcJjvEJYz2hHCxSs1uozaMZwXBjxq53vSldMhRfZ2p1OxZR2o3iCkTc0G7apjITdnmHXR8xlde2yd1LOvhvivURMElSTfmD4FgAXEUT55k3OFmF6g73y3JPFlAIQ2al4dnuefTFGujJhqmWSlrU35OUaIly8QBWcXKucxspV', 'whRJcxN2', '2016-09-09 03:41:00', '2006-06-04 03:56:00'),
(29, 'CwoNua77V10v', 'ZOdrWFs4SAIc5lSqAkqNM7XHIF0mtJyuPMuV03dvoUi4Rr3uMs', '2018-02-13 05:10:00', '2013-04-18 00:39:00'),
(30, 'UHvHtsDdZR0noIeBOMjtYmUQJb70lEnzxY2WBjZ1Y7rqOkVBF1eyqR1hYvtYfYcrWNpYiWpt7FZK2tvIURTcCZRONhHTpVRTdTvl4U', 'GQEAxgCMV5SfeT771oBIpL0cNsw4fpKmlrIwhhuXO3M5x4k4RZ8szpo6QbIWJ1P5mNiuzsHusBnDn0g1QC0Nmf5C8r1XssiusYPG51wkfxoEEhLlIdAxzkELEMm1alHAPCEswLIfETUBZtyHmBOcEzNprFhqyTeuktm37KUhrHXgRbqbvhgpXBq2hXyPRixgBG', '2003-12-16 00:43:00', NULL),
(31, 'UOU4xAZI5hVF21dVCjgMAuPbJ5hV5Tf4ynFcozuBsn6uffNJAVCHEkFwyINteIiLGlVeutHNtItStK5fTkjOk', '5USlQt8iw258xkSiJ7OCkoh0s3rw4eZO1m1PMVHqU03V1muulEbjb', '2014-12-25 00:12:00', NULL),
(32, 'sJORMqz1QZOBKk1KdXoyDfox4M42NztULyAmsba1b7FttaYclGlTL6jRoP5lXmcrc0ZkdbUcTqOfAJV8vRlaDp8l5EWU1U0ziS6coSlgz4RUJvqYNS3xlenIqyoIyRMVl8zksWgAulshh4BThQ6PGDnuFYxhLQ0AcF5oTw1FCfqQPnouRcb3SJKe3c4WSVZT3gtxsgFX8l1yRtzaTBsL6X2ROLcV8dZ1MW7ZlCXmyGjrs', '5pYgWBTocCejF1PAbCf8eP1rjho5kovW5s7uxx83kbTiWiOsT2zOJEMkee2lP5srD8R0r0y2Z1B553UQzUP4TnpWBKVmbrgFA7Mw7cEHAqY43EIrzFKCwVFhylwE', '2019-04-20 03:51:00', NULL),
(33, 'tQiJFOF2XH5aevMP46QadheWazXSR', 'dTU55v4oN3GdzcHDvjitRTOTgDLN2mXtWW5e35mKJgx8UUESyepfUvvMbN1InceKNPMcO3CWIfruvPdoIn0kIQmoye0DkjwP4ZBP', '2021-05-19 05:39:00', '2024-01-26 04:12:00'),
(34, '2eek1vhri5EZQRFf88QU4POlojuPSowB4MUkgZdyn26dACuI2IleS6p5aOY8tFqgu3dWBubA58pMEc1Cp0wr6t3XVty1kuCB', 'I38t5DL5exiA2HE3PgAmpxuwHSdTGmq0gOf7LPEKTxwWYIY7PPC0bHu86EYM3cSYJeUVMAFKPYacBLoC2KXU16C8fR5b3sCTMknhF03PsZLUVZMbBzVtL62QEwdwp14EC', '2007-02-12 00:19:00', '2003-03-18 22:52:00'),
(35, 'mUUXtdY7gu2b7AASPMGNX8CYIv6wjDogBbPmJmkPi2rbL7VKXGtT3edtyqbA2dEwg4TH33dYHIBPfCgziBfTFj4hpPEkoG3alw6PWhWFCauC0enLDexC6IDfK3IUQep60NgmryF0wLuTAvGZIRjW0VcwYOeQXqCpe2gHUpX4C8e624kPbYT0vqTJrqINOXg5hqYwDfsSfPxrLRW334sYUl3fFTBdNy3qiblxaaGSg4UWAcd1pCts', 'NVUlpGNrnqQAEmL68vAUkcQRRRrQRPCyMZY02pAkSJV1ivENH0sYZPr5xuYxXXWFGXq1Emawk8ubRhtSbe5mgfrq8NKGKzIsHfBJEZMzTBM', '2005-05-26 06:00:00', '2009-09-08 23:22:00'),
(36, 'duhSOJPDMb4GQlPJL673kUIBRAh7FulPD10AvWYQLcqh6dRQc4sJ3am7lZupdRbu1ptILqX4qswj2gaUXhqwhni8SuzlpNa6wkaZUZIh0D5kGzRutx6suqqvxfVZOgzbJgx56nVCWWy1WaWw4dv2TuwMbDNXfOGxwISbW6et8UdUpzsMQnIvCoBzZwrAJUoRHx8JHBQ3JBvZRa6BkuSfR1P3v', 'PWlK0aVllwSDNhCxi453d1SrSOTWg5jZ', '2001-03-06 07:26:00', '2012-07-18 21:42:00'),
(37, 'GjTdvWKAELoOT42hl6mVGKo6yuGQImBSeciPvSNyWKrTyxyt6G0glk0hTPx1F8lCLt4XNLwt82txyfJ8w8SibpKd5tke8Pw5rdseBw0ttrQoiTDm5yZP', 'l1TfWjU0IhEuXg1CGSQBH1YGUtyVlzBlSjDjjUTEZI7R3bZPCxxE5HGiiWyzonuv7P6aN0J1ELEIr6fER1VWJDbXIRN3buNjdjFouYQtQ2jsCx5tNBDkO8wXUoqO6UmArOJICxS30bqe22HnCTTGBSYafFJ4wNbzhE42gDJpbBFkn8d2lt3nmBb5jTjz4icVRKqBAfCWitTNTenxknaRuQK4LTEGfO6VcNbuDRQxS', '2011-11-26 01:11:00', '2025-05-01 07:36:00'),
(38, 'BsMpDlUHv7QRunHNh4f17GnzJBktXDs80ezYgopjHghvAsSm5qQcJfbEsaxQbDWt5u0j', 'JvoZFaUiKukQts8WWTrnURn7wle7V1kz8JHXdYnhZYroePoQlMzBsRGjkmtbyG4ZbjQ768vc5lwqQASoeiH4RENZHPiyUn6EU1dDrGCUZidWbiJzhHXg08EDcCCOkaGiE36wLe4GmZlEI3u62YblB7i6ftvXt5ZATE1mjqymtrCYaDwcagAD5XhTrJ2saIxrxfcutVEsGiLPiMuwJoFV4wlBEw', '2001-07-07 22:47:00', '2014-05-29 07:31:00'),
(39, 'fo4i7sC8nVvo5bR3Hs2OQand73', 'vTJl6WgEnrxpnRZmXo6E1JLO78kwnS4yb7j2VfsJmdPBkzsngyoZBFgPeDK3V4fhBOTeWPE1Cc73CvLgbNOxqVcMH0TkGxkWOJCRiEkrjpyp0YhiGDMjixQy85g2gLMzs5TyEMJaZ4w1Eo2Sf5DHdnD3sNwyPQ5B1it', '2023-12-24 06:26:00', '2000-07-13 05:20:00'),
(40, 'PCmSBKiMHApW', 'HfMfWh3OEWr48lsCsQbgX3W4EILDZjGEQzupbIEYlzMYgidRItBidT0nKJLVhfkgPWIPCmsCi223EzTF3uOtCWOJrUry2CRZXKgCbRODY0wakZCWAatkVgjzT6GsPb3fILUzAItcn1Npn8dJ1ltlfYL2W0kGbfCEvIepQHfcpUHPKkRKMrP4HsKuKtQMToC2IFDUPtY', '2024-11-03 23:29:00', '2010-05-16 03:47:00'),
(41, 'vvz8JWxc5jKoWk8WrxaTpI4BJpG2b2CvuddcYgmy6pfPJJ84DGSLoMDF3KCwSGe68JzSEe8mCVScZCYJTofXZj2FFne4BOUHpqcAPsJ4r5AgzQ0KgqrsXitqLXh1fstMu5do2Eu3kTITGmVdZpcAtiYH0I0HF80eD57uP7VRNTI5HmUShtOSAzEnTAyDTxDi2u5c0hfRNov1C7XHl4x0v', 'pDq5VIZTYfvoDCrd0IjZYo7IAmVYU7RPZGTJAX5SuJMmZk', '2024-11-16 03:08:00', '2015-10-07 23:03:00'),
(42, '5jsSyXnT8pXifKEfQ6aCXCx', 'SBjTSq0fjjPRRbp4kZzqlKiJSbrdkaBMDSvN8gBDFgag6WyTqe', '2000-01-16 08:18:00', '2018-05-11 22:00:00'),
(43, 'MJzyC6ldnckYDA5naUDLrnPFIYsLPFCISfMgYkRCqZKiBl4rciWGZft0SwipESgHzSpgkL7VYbXHVrEATuAN0a8AYVLEkn4Nr1wbYK12soSR1FdFTy8c1f5heuiYoqBe3LrokfBHCoBWTN1piRWYwNttnQKBvR1Z3KLyWJ8STCoovbjUomILf6pglOhEYMfXwuk7TifrqJBZv', 'jYhVZYuuUV0INhlawld4MDJw1gnM', '2021-03-31 01:06:00', '2025-01-23 23:17:00'),
(44, 'iHFHMSGUY6xqzu5n3zhnmLlb8bbbkC3qbLLUwOXwwXh4EU0CMlPBxLwA0ZOhFePzQGP6QN5', '6rZw0dBdBi0VY0QvDKd1uTEC1uprRKLCCDIb5INgEP8cXIEUO7C8hmJE7IMa2f3zrFn34pD7ynvLfBaqqGJuRau5TnY7H6nTQG3f1KHqUoBWcc3KbjvTekgz04WxhanRSSKZKuiQskAk5TpOfJud8Exf8xQQlXgEn0vSolV1ygEkidFRtfswsYbCWG', '2012-09-28 04:41:00', '2017-12-26 05:07:00'),
(45, 'hEBfSCzukESY1YbvqDc2VNk25szTs7BNC2RmHzlmsWPFxeupO5bM6VkmIbQYgDFMllM0ZCdrHWCtZGLyinMkLBMxEWyt37jtTngRvhQUkWuSajEIWsU', 'ivxKuGRWTwYsLvYuIoXeZEUUGRzrqe4qAhQzwk1czIurt4aELrjv8OiQhKak7aqdiEuYwDABPeUDkzgoM3v7oX8c1uHwdlvXrlLxt8tzWMaeMlxFd2B6IocJEAbflLaMgz4WvJVKu7sI5oHcHQGrZCRg01S4UspI6xHoztxgAkPOWArOTgzSECXs1nIn0qGN6WxvRTcUqvdS57VQbkUH', '2007-09-19 04:14:00', '2013-12-22 23:58:00'),
(46, 'QqTKJxODkOJbLu1wYVz3IzIq00zsxvzYeDam56C4QwIKCkXjYkyOnoaddIDF5twz0dFlQC3glAuIBnAeRYOSV0aW86ApDQlYR1Y4Pf1XV6R8sXwe3YXSRFl4CmUF2lcY2tCn8KXtHUGlejk3BA', 'VELvii5Op5y6GIE', '2016-10-15 04:31:00', NULL),
(47, 'YWMpzgpSlpw1jl7TvH3GGTov81ZrFCnKNRaeBhsdRtCyUyVcjFhHW3rL1ju5enUfsd54pcj5gpdHKVlywuGlbKmWAoxPv1K3u35JzrDS3uyQuIPeJRCU2wtV2tXApDRcNKo6KUzEi3Ml5AuTfyvy6TBTVqbB3sXtxIDtxTlfC4UYUfCXdwcneQkLIa0gdo2I', 'dDeHgQqeB0naVN8tdZApSroKampOoPgFjYOKDRWxIaAsV5vqmXRCRIVt2q7O44TzaCu7orSxEW01Za86gLS6eihLPpcNxmHTauCRCJwqfzdDj', '2003-02-28 08:56:00', NULL),
(48, 'N7RqBti5k4texhmCaNDOVeNCerjEdIsDqg6YWAxYLX5UPaP6RXMYRYuK', 'C2ZHXfLz3PuLdhsLOEvG5ROTHPP3kHNRJZWmGDFe2G4d8zDQbVHpZtKOHoVrIxI5BZvojE6t6ebZLfTrHrvm788PXzQdotjdUJemCRC0V7iOBCE1kkiJuSItCxe7YiWxSvZDRiZE6pusjthjQYceC7qyT1WlC3IYhIO8MIwR3XTEBbIJLJhxCNUlOuLRHww6q1cIEShuwmCAKsiGsXN', '2024-04-07 01:58:00', '2007-11-21 04:13:00'),
(49, 'qBeGat1SoTaHu7iHVfOCQYP7MMmOiSVueeA3POAN2ROsNau8XA5SiYTCvVeAXcJZUnosj6XtzqPI5Is0zYGPPx7QRDQEDiQBe', 'onDbqy47D', '2001-02-18 01:55:00', '2012-02-14 03:27:00'),
(50, 'GZWqSD2H0sWtfNpZiCaAjGqevHSe1DIvLjhDGqhhWGOct1Le5OlNEjV7GniqUXk5bC3P', 'cQaskHzpSHGVY6iffivXUC7eJDb3UdGSIzLF1ElnLKyRWSJivWxYhZETXSXinNtpBnxWmqPOJJUlXSheB1pBXU8Cib1lBlR8L0Ff3Vvo0FMyQf6tryxYne8EXzdlVieupEUSQFMcvHFawAnS2RPUUA18YM', '2007-07-26 07:26:00', '2005-01-17 01:40:00'),
(51, 'ogsignGXFzQtBVD2qEwOwDn5lYCOm2QexPocAoeDsypEifgqHMZgqs5zUocMopRgW507mjRe8iB8NX7mpV8Zdw1L4Kk11XL1TDNGodNJRMIOEnIqGC3iSOot1OtFTQJXmZzkMMYdY8XZZyKwvqmU7JsofkPBt45yyuENcSAwnUhZPMxubExjG1ySjZ8F18yRbvfnpRFO5KumiHczV2kCBrTrXrV', 'bv12t1clY10yceZjHAXt', '2006-04-07 04:47:00', '2021-10-01 03:57:00'),
(52, 'k1uL4VR3zV7Xlk4nVGHdNTVHRBgHcyzPWlFLFeeVsZcS5TKSf5g2b8ZFL0ZWfnJK8EWr3jjDEJ2SM8Ul0RKCpuWA51ymtLVYsdOkl5khasyOrzuU1bmM7mGHZnBA4IXiUxuzmjqFejqfrzT8SXZEGk7sWEq3gIylKXhTXfa3', 'TlsBuiwDLsm5VVIiFca4ZPWFxkIBUczL4d6KrGPHQk1YBccT8oybvcSQ7UO8K6fYoT5ACszJqJbrqKlqodgW62RbOQtHefsQhOmVsyW0Si2oZVgVp5uvj7bqeT6dUd2L1nI', '2012-11-26 22:27:00', '2007-11-11 01:43:00'),
(53, 'LJbr3bKl8xkR0wVocoTQOqysgXkFabBhc2ubgAPBkQRx2e', '15JqqqIAYCHNLy2GL4bgdqRzaqkVOR6Ef1KSiYwhHoReYUhuwnvUPl2rkW1tFXMRnf1AYFHtpRn820HoKWYlQH5dHafam0P4VLcCNQqneaJ3obaGEQeQSiqUY2xuuVVM301DOzX4rVGw6bTcomGbBuLiSUh2cA2ymVHipk6SSoPHniaKKa', '2000-03-07 04:13:00', '2003-09-22 01:13:00'),
(54, 'wxinR01G2BLsXl5ZYilaSOAXwuexKG4Uuu5hI86xosroYvQmOZa', '7AtPflsbmoEE0L8FGjQITL3vdRudpAKvWucllngidW6VKnWIuwzytLdTajAa1RRqxEPAgifRfr8l4yKGJoEidMOlCE5lyypNESMHjUVDOEUJWFkcLySlUesVQ2OI33IBpeto18f4nj5HtKCKr5mL3S6aUmgBg', '2021-12-30 00:37:00', NULL),
(55, '0gR32a70lXqOwlm6Ysv0R', 'a6KgY', '2000-09-29 05:05:00', '2022-07-11 00:07:00'),
(56, 'Bmu2bTmewZLPWLL6VX2QjO4R6jIvdYjNuR2Rb0eSckYcCKHPrP7C4th4zPHtFrgidzy8EwOz0uppM2rnTuoygYiW87LXeeX4ITRCgb4j5bPw1CoaXrN8QzTvxIwU7K37IvKlq1DWnStJkNkeR4xxQm7NoGjddzBcUtsfC6BZTIfTLO5Of6CrNhH4D5WaKiLUB', 'gkkPi48s6QwUAFYoyg6VzDOk4YJm4bFs4m6dNGeBTisjMFMkruMIdVeMv3YTVdgGfF20XRj5E0EzcgF5jwdRdaKrZLCJVFZt1s4qxMVmZUzj1IlbpUWoC', '2023-09-30 01:36:00', '2009-03-27 01:27:00'),
(57, 'cPKOtokPKcJgrpK7hZI57kn6p8Zn34gmSiR8nj2UPWfxFTXVq3gzcHLmHillwJp8AWnX5HMR8FEoSOpIJEktNfrtvyn5jUbhrfqSHgqHnaeLAPg6nHjqAV', 'PVEmpkn3u0Xn0IlYD4YDdFHaAcRjQCK6', '2022-03-25 07:08:00', NULL),
(58, 'TktodvlHChxz1Nh7002Y3klCNjg51u6GKcMtTASDQLq', 'R0dY67YozrydlvIha5VGNJTr5Py0WPq6oGZz380nuM1HsusWoYL45YTdG6oU8JqiIKYv3VJrEjxcIV2igtco01Qbc3pkIhlQEURI4y5PeP8NZqri5', '2016-06-14 07:33:00', NULL),
(59, 'pdxpMX1UDKtXJmGueSjonHKWXTX3zYdNVdMHMtzzV5tBLi', 'UpLUVvtUcggF', '2007-12-28 04:20:00', '2008-11-14 07:40:00'),
(60, '0L7GRjmWnS26ZBCNh1JvAaUnl3ACycumQgGmYxzx8oHHnuJIBhTmjRd2pvP3jpaRIo3kPXHzk7sHA6ukB3cLeHt06PWCLzJg2gLDztaa63o0HlUkcWrPk760TNW7jasExbsdzSlBbdV7nx3bFWEGxzOFg2b6okmD8QmuiEngGGwK7UEooITrWQctbuHuqYOVphH2gTDHOAhStSLJTcdhNBHiNjjHe8y', 'zZjEux7mPMYpMSSNPYHtHKG6oKXBNuCKfCOoixj54ZnGMXlqmJtVipz0DlgUQRCMLleAGHo0EotvuScjkOGrg4WhlbQXbeYK8gf5wkt2Nmjbu7FvGNX1rPHJN0FcA8VFkUXFEMv6lRcZB2cDsaVJPmR2FiK8UZLIppzOdVpr2KsxPKwyLxh', '2017-11-25 01:43:00', '2000-12-19 23:06:00');

-- --------------------------------------------------------

--
-- Table structure for table `product_skus`
--

CREATE TABLE `product_skus` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size_attribute_id` int(11) NOT NULL,
  `color_attribute_id` int(11) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `Currency` varchar(3) NOT NULL DEFAULT 'EGP',
  `quantity` int(11) NOT NULL,
  `loyalty` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_skus`
--

INSERT INTO `product_skus` (`id`, `product_id`, `size_attribute_id`, `color_attribute_id`, `sku`, `price`, `Currency`, `quantity`, `loyalty`, `created_at`, `deleted_at`) VALUES
(66, 1, 2, 6, 'WH-MEDIUM-WHITE', 1000.00, 'EGP', 30, 0, '2025-03-26 08:16:32', NULL),
(68, 2, 1, 6, 'IE-SMALL-WHITE', 59.99, 'EGP', 80, 0, '2025-03-26 08:16:32', NULL),
(70, 3, 2, 6, 'FS-WHITE', 999.99, 'EGP', 15, 0, '2025-03-26 08:16:32', NULL),
(72, 4, 3, 9, 'MR-GOLD', 499.99, 'EGP', 30, 0, '2025-03-26 08:16:32', NULL),
(73, 5, 4, 4, 'GL-XL', 1499.99, 'EGP', 10, 0, '2025-03-26 08:16:32', NULL),
(74, 6, 3, 4, 'UL-LARGE', 1199.99, 'EGP', 15, 0, '2025-03-26 08:16:32', NULL),
(76, 7, 1, 8, 'FW-BLUE', 299.99, 'EGP', 50, 0, '2025-03-26 08:16:32', NULL),
(77, 8, 2, 9, 'LW-GOLD', 499.99, 'EGP', 30, 0, '2025-03-26 08:16:32', NULL),
(79, 9, 3, 7, 'BS-RED', 99.99, 'EGP', 60, 0, '2025-03-26 08:16:32', NULL),
(80, 10, 4, 5, 'HT-BLACK', 799.99, 'EGP', 20, 0, '2025-03-26 08:16:32', NULL),
(81, 11, 3, 8, 'FW-BLUE', 102221.00, 'EGP', 123, 0, '2025-03-26 08:26:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `promocodes`
--

CREATE TABLE `promocodes` (
  `id` int(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` varchar(20) NOT NULL CHECK (`discount_type` in ('percentage','fixed')),
  `discount_value` decimal(10,2) NOT NULL,
  `min_cart_total` decimal(10,2) DEFAULT NULL,
  `max_uses` int(11) DEFAULT 1,
  `max_uses_per_user` int(11) DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promocodes`
--

INSERT INTO `promocodes` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_cart_total`, `max_uses`, `max_uses_per_user`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(1, 'WELCOME10', 'Welcome discount for new customers', 'percentage', 10.00, 100.00, 100, 1, '2024-01-01', '2025-12-31', 1, '2025-09-03 16:51:21'),
(2, 'SAVE20', '20% off on orders over 200 EGP', 'percentage', 20.00, 200.00, 50, 2, '2024-06-01', '2025-12-31', 1, '2025-09-03 16:51:21'),
(3, 'FIXED50', '50 EGP off on any order', 'fixed', 50.00, 150.00, 200, 3, '2024-01-01', '2025-12-31', 1, '2025-09-03 16:51:21'),
(4, 'SUMMER25', 'Summer sale - 25% off', 'percentage', 25.00, 300.00, 75, 1, '2024-07-01', '2024-08-31', 1, '2025-09-03 16:51:21'),
(5, 'STUDENT15', 'Student discount - 15% off', 'percentage', 15.00, 50.00, 500, 5, '2024-01-01', '2025-12-31', 1, '2025-09-03 16:51:21'),
(6, 'VIP100', 'VIP customer - 100 EGP off', 'fixed', 100.00, 500.00, 25, 1, '2024-01-01', '2025-12-31', 1, '2025-09-03 16:51:21'),
(7, 'EXPIRED', 'This promo code has expired', 'percentage', 30.00, 100.00, 10, 1, '2023-01-01', '2023-12-31', 0, '2025-09-03 16:51:21');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Super Admin', 'Full access to everything, including managing roles and permissions.'),
(2, 'Admin', 'Manages users, products, categories, orders, and reports.'),
(3, 'Seller', 'Can manage their own products, view their sales, and process their orders.'),
(4, 'Warehouse', 'Manages stock levels, updates order fulfillment, and tracks shipments.'),
(5, 'Customer Support', 'Can view orders, assist customers, and handle returns/refunds.'),
(6, 'Marketing', 'Manages promotions, discount codes, and marketing reports.'),
(7, 'customer', 'normal Customer');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `can_read` tinyint(1) NOT NULL DEFAULT 0,
  `can_write` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_categories`
--

CREATE TABLE `sub_categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_categories`
--

INSERT INTO `sub_categories` (`id`, `parent_id`, `name`, `description`, `created_at`, `deleted_at`) VALUES
(1, 101, 'Over-Ear Headphones', 'Comfortable over-ear headphones with noise cancellation.', '2025-03-24 10:25:29', NULL),
(2, 101, 'In-Ear Earbuds', 'Wireless and wired in-ear earbuds with deep bass.', '2025-03-24 10:25:29', NULL),
(3, 102, 'Flagship Smartphones', 'High-end smartphones with powerful processors.', '2025-03-24 10:25:29', NULL),
(4, 102, 'Mid-Range Phones', 'Affordable smartphones with excellent performance.', '2025-03-24 10:25:29', NULL),
(5, 103, 'Gaming Laptops', 'High-performance laptops for gaming and creative work.', '2025-03-24 10:25:29', NULL),
(6, 103, 'Ultrabooks', 'Slim and lightweight laptops for productivity.', '2025-03-24 10:25:29', NULL),
(7, 104, 'Fitness Smartwatches', 'Smartwatches with advanced health tracking.', '2025-03-24 10:25:29', NULL),
(8, 104, 'Luxury Smartwatches', 'Premium smartwatches with elegant designs.', '2025-03-24 10:25:29', NULL),
(9, 105, 'Portable Speakers', 'Compact speakers with Bluetooth connectivity.', '2025-03-24 10:25:29', NULL),
(10, 105, 'Home Theater Systems', 'High-quality sound systems for home entertainment.', '2025-03-24 10:25:29', NULL),
(11, 106, '4K TVs', 'Ultra HD televisions with smart features.', '2025-03-24 10:25:29', NULL),
(12, 106, 'OLED TVs', 'Premium OLED TVs with vibrant colors and deep blacks.', '2025-03-24 10:25:29', NULL),
(13, 107, 'Mechanical Keyboards', 'Responsive mechanical keyboards for gaming and work.', '2025-03-24 10:25:29', NULL),
(14, 107, 'Wireless Keyboards', 'Bluetooth-enabled wireless keyboards for convenience.', '2025-03-24 10:25:29', NULL),
(15, 108, 'Ergonomic Chairs', 'Comfortable chairs for office and gaming.', '2025-03-24 10:25:29', NULL),
(16, 108, 'Standing Desks', 'Adjustable height desks for a healthier workspace.', '2025-03-24 10:25:29', NULL),
(17, 109, 'Security Cameras', 'Surveillance cameras for home and business security.', '2025-03-24 10:25:29', NULL),
(18, 109, 'Smart Door Locks', 'Keyless entry smart locks with fingerprint scanning.', '2025-03-24 10:25:29', NULL),
(19, 110, 'Electric Toothbrushes', 'Rechargeable toothbrushes with advanced cleaning technology.', '2025-03-24 10:25:29', NULL),
(20, 110, 'Hair Dryers', 'Powerful hair dryers with multiple heat settings.', '2025-03-24 10:25:29', NULL),
(21, 111, 'Wireless Chargers', 'Fast wireless chargers for smartphones and accessories.', '2025-03-24 10:25:29', NULL),
(22, 111, 'Power Banks', 'Portable power solutions for charging on the go.', '2025-03-24 10:25:29', NULL),
(23, 112, 'Smart Scales', 'Body composition scales with Bluetooth connectivity.', '2025-03-24 10:25:29', NULL),
(24, 112, 'Fitness Trackers', 'Wearable fitness tracking devices with step counting.', '2025-03-24 10:25:29', NULL),
(25, 113, 'Blenders & Mixers', 'Kitchen appliances for smoothies and meal prep.', '2025-03-24 10:25:29', NULL),
(26, 113, 'Coffee Makers', 'Automatic coffee brewing machines for home and office.', '2025-03-24 10:25:29', NULL),
(27, 114, 'Smart Bulbs', 'Wi-Fi-enabled LED bulbs with adjustable brightness.', '2025-03-24 10:25:29', NULL),
(28, 114, 'Smart Plugs', 'Remote-controlled power plugs for home automation.', '2025-03-24 10:25:29', NULL),
(29, 115, 'True Wireless Earbuds', 'Completely wireless earbuds with high-quality sound.', '2025-03-24 10:25:29', NULL),
(30, 115, 'Bluetooth Adapters', 'USB Bluetooth adapters for seamless wireless connectivity.', '2025-03-24 10:25:29', NULL),
(31, 116, 'External Hard Drives', 'High-capacity storage solutions for backup and file storage.', '2025-03-24 10:25:29', NULL),
(32, 116, 'USB Flash Drives', 'Portable storage devices with fast transfer speeds.', '2025-03-24 10:25:29', NULL),
(33, 117, 'Smart Thermostats', 'AI-powered thermostats for efficient home climate control.', '2025-03-24 10:25:29', NULL),
(34, 117, 'Home Assistants', 'Voice-controlled AI assistants for smart homes.', '2025-03-24 10:25:29', NULL),
(35, 118, 'DSLR Cameras', 'Professional cameras with interchangeable lenses.', '2025-03-24 10:25:29', NULL),
(36, 118, 'Mirrorless Cameras', 'Compact cameras with high-quality image sensors.', '2025-03-24 10:25:29', NULL),
(37, 119, 'Laptop Stands', 'Ergonomic stands for comfortable laptop usage.', '2025-03-24 10:25:29', NULL),
(38, 119, 'Cooling Pads', 'Laptop cooling solutions for better performance.', '2025-03-24 10:25:29', NULL),
(39, 120, 'E-Readers', 'Lightweight digital book readers with e-ink displays.', '2025-03-24 10:25:29', NULL),
(40, 120, 'Tablets', 'Portable touchscreen devices for work and entertainment.', '2025-03-24 10:25:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tax_rules`
--

CREATE TABLE `tax_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `country` varchar(2) NOT NULL,
  `state` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `start_date` date DEFAULT curdate(),
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tax_rules`
--

INSERT INTO `tax_rules` (`id`, `country`, `state`, `category_id`, `tax_rate`, `start_date`, `end_date`, `is_active`) VALUES
(1, 'EG', NULL, NULL, 14.00, '2025-01-01', NULL, 1),
(2, 'US', 'CA', NULL, 7.25, '2025-01-01', NULL, 1),
(3, 'US', 'NY', NULL, 8.88, '2025-01-01', NULL, 1),
(4, 'US', 'TX', NULL, 6.25, '2025-01-01', NULL, 1),
(5, 'UK', NULL, NULL, 20.00, '2025-01-01', NULL, 1),
(6, 'DE', NULL, NULL, 19.00, '2025-01-01', NULL, 1),
(7, 'AE', NULL, NULL, 5.00, '2025-01-01', NULL, 1),
(8, 'EG', NULL, 129, 20.00, '2025-01-01', NULL, 1),
(9, 'EG', NULL, 130, 20.00, '2025-01-01', NULL, 1),
(10, 'US', 'CA', 102, 8.50, '2025-01-01', NULL, 1),
(11, 'EG', NULL, 112, 5.00, '2025-01-01', NULL, 1),
(12, 'EG', NULL, 110, 5.00, '2025-01-01', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `Google_ID` bigint(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `f_name` varchar(255) NOT NULL,
  `l_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `birthdate` varchar(255) NOT NULL,
  `phone_no` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `gender` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `role_id` int(11) NOT NULL DEFAULT 7,
  `loyalty_points` int(11) NOT NULL DEFAULT 0,
  `Loyalty_status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `Google_ID`, `avatar`, `f_name`, `l_name`, `email`, `password`, `birthdate`, `phone_no`, `created_at`, `updated_at`, `deleted_at`, `gender`, `status`, `role_id`, `loyalty_points`, `Loyalty_status`) VALUES
(2, NULL, 'uploads/gelan21867@baxidy.com/avatar.png', 'ahmed', 'ali', 'gelan21867@baxidy.com', '$2y$10$lQroJV.dNeUMj/bZ4MUYqeLEcGtwv5Be3K01FdAt1.n8aVy0FiHZu', '2000-12-15', '01286246292', '2025-08-15 20:20:37', NULL, NULL, 'male', 'active', 1, 0, 0),
(4, NULL, 'uploads/gitoni4718@besaies.com/avatar.png', 'gu', 'II', 'gitoni4718@besaies.com', '$2y$10$rb/wc6fIQICVLg6TGyv4e.Ew5KbbntzGXn1WI7E.NXMsROJlP4M4K', '0222-12-12', '122', '2025-08-30 15:16:55', NULL, NULL, 'male', 'active', 7, 0, 0),
(14, 9223372036854775807, 'uploads/profile_pictures/33864171114794816dbc188b7f979d29.jpg', 'OMar', 'Khaled', 'ok3050802@gmail.com', '$2y$10$3n.d4xcQiWak54pxDCYxIeeaiQ37bWBHiFhG6N1NBmdwdYs615xdG', '18-10-2000', '01286246292', '2025-09-03 20:27:53', '2025-09-04 15:16:35', NULL, 'male', 'active', 7, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_promocode_usage`
--

CREATE TABLE `user_promocode_usage` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `promocode_id` int(20) UNSIGNED NOT NULL,
  `used_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_promocode_usage`
--

INSERT INTO `user_promocode_usage` (`id`, `user_id`, `promocode_id`, `used_count`) VALUES
(2, 14, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `whishlist`
--

CREATE TABLE `whishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `whishlist`
--

INSERT INTO `whishlist` (`id`, `user_id`, `product_id`, `created_at`, `deleted_at`) VALUES
(52, 14, 5, '2025-09-04 11:22:14', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_sku_id` (`product_sku_id`);

--
-- Indexes for table `cart_promocodes`
--
ALTER TABLE `cart_promocodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `promocode_id` (`promocode_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_ID` (`product_ID`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cart_id` (`cart_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_sku_id` (`product_sku_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `function_name` (`function_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_skus`
--
ALTER TABLE `product_skus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `color_attribute_id` (`color_attribute_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `size_attribute_id` (`size_attribute_id`);

--
-- Indexes for table `promocodes`
--
ALTER TABLE `promocodes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `tax_rules`
--
ALTER TABLE `tax_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `Google_ID` (`Google_ID`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_promocode_usage`
--
ALTER TABLE `user_promocode_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `promocode_id` (`promocode_id`);

--
-- Indexes for table `whishlist`
--
ALTER TABLE `whishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart_promocodes`
--
ALTER TABLE `cart_promocodes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `payment_details`
--
ALTER TABLE `payment_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `product_skus`
--
ALTER TABLE `product_skus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `promocodes`
--
ALTER TABLE `promocodes`
  MODIFY `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_categories`
--
ALTER TABLE `sub_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `tax_rules`
--
ALTER TABLE `tax_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_promocode_usage`
--
ALTER TABLE `user_promocode_usage`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `whishlist`
--
ALTER TABLE `whishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`),
  ADD CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `cart_item_ibfk_3` FOREIGN KEY (`product_sku_id`) REFERENCES `product_skus` (`id`);

--
-- Constraints for table `cart_promocodes`
--
ALTER TABLE `cart_promocodes`
  ADD CONSTRAINT `cart_promocodes_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`),
  ADD CONSTRAINT `cart_promocodes_ibfk_2` FOREIGN KEY (`promocode_id`) REFERENCES `promocodes` (`id`);

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`product_ID`) REFERENCES `products` (`id`);

--
-- Constraints for table `discounts`
--
ALTER TABLE `discounts`
  ADD CONSTRAINT `discounts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD CONSTRAINT `loyalty_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order_details` (`id`),
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_item_ibfk_3` FOREIGN KEY (`product_sku_id`) REFERENCES `product_skus` (`id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD CONSTRAINT `payment_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order_details` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `sub_categories` (`id`);

--
-- Constraints for table `product_skus`
--
ALTER TABLE `product_skus`
  ADD CONSTRAINT `product_skus_ibfk_1` FOREIGN KEY (`color_attribute_id`) REFERENCES `product_attributes` (`id`),
  ADD CONSTRAINT `product_skus_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `product_skus_ibfk_3` FOREIGN KEY (`size_attribute_id`) REFERENCES `product_attributes` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`);

--
-- Constraints for table `sub_categories`
--
ALTER TABLE `sub_categories`
  ADD CONSTRAINT `sub_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `tax_rules`
--
ALTER TABLE `tax_rules`
  ADD CONSTRAINT `tax_rules_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_promocode_usage`
--
ALTER TABLE `user_promocode_usage`
  ADD CONSTRAINT `user_promocode_usage_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_promocode_usage_ibfk_2` FOREIGN KEY (`promocode_id`) REFERENCES `promocodes` (`id`);

--
-- Constraints for table `whishlist`
--
ALTER TABLE `whishlist`
  ADD CONSTRAINT `whishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `whishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
