<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\News;
use App\Models\WholesalePack;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed News
        News::create([
            'title' => '🔥 Database Refresh: New Fresh GB / FR / BE / US Bases Added!',
            'category' => 'Database',
            'content' => 'We have just uploaded over 15,000+ brand new cards with high valid rate (95%+). Bases 2026_08_19_GB_FR_R and 2026_08_18_US_UK_MIX are now live with instant auto-refund enabled.',
            'is_pinned' => true,
        ]);

        News::create([
            'title' => '⚡ System Update: Instant Automated USDT & BTC Top-up',
            'category' => 'System',
            'content' => 'All deposit gateways are now running at 0-conf speed! You can top up your balance and start purchasing immediately without waiting.',
            'is_pinned' => false,
        ]);

        News::create([
            'title' => '💎 Wholesale Discount: Get up to 35% OFF on Bulk Packs',
            'category' => 'Promotion',
            'content' => 'Check the Wholesale tab for massive discounts on pre-sorted US, UK, and EU packs.',
            'is_pinned' => false,
        ]);

        // 2. Seed Wholesale Packs
        WholesalePack::create([
            'title' => '50x UK Mixed Debit & Credit Pack',
            'description' => '50 Random fresh UK cards (Revolut, Barclays, HSBC, Monzo). 100% with full address & phone. Valid rate 92%+ guaranteed.',
            'card_count' => 50,
            'price' => 65.00,
            'original_price' => 100.00,
            'country' => 'United Kingdom',
            'type' => 'Mixed',
        ]);

        WholesalePack::create([
            'title' => '100x EU Mega Bundle (FR, BE, DE, IT)',
            'description' => '100 Verified European cards from top tier banks (BNP, Credit Agricole, Deutsche Bank, Belfius). High balance hit rate.',
            'card_count' => 100,
            'price' => 120.00,
            'original_price' => 200.00,
            'country' => 'Europe Mix',
            'type' => 'Debit & Credit',
        ]);

        WholesalePack::create([
            'title' => '30x USA High-Tier Platinum / Signature Cards',
            'description' => '30 Premium Chase, Citi, BofA Platinum & Signature credit cards with Full SSN, DOB, Address, Phone, and User-Agent.',
            'card_count' => 30,
            'price' => 75.00,
            'original_price' => 110.00,
            'country' => 'United States',
            'type' => 'Credit (Fullz)',
        ]);

        WholesalePack::create([
            'title' => '25x Canada & Australia Mixed Pack',
            'description' => '25 Fresh RBC, TD, CommBank cards with full details. Instant replacement policy.',
            'card_count' => 25,
            'price' => 45.00,
            'original_price' => 70.00,
            'country' => 'Canada / Australia',
            'type' => 'Mixed',
        ]);

        // 3. Seed Tickets
        $t1 = Ticket::create([
            'ticket_number' => 'TCK-88301',
            'subject' => 'Question regarding auto-refund policy',
            'department' => 'Billing',
            'priority' => 'Medium',
            'status' => 'Answered',
        ]);

        TicketMessage::create([
            'ticket_id' => $t1->id,
            'sender' => 'user',
            'message' => 'Hello, if a card is declined or dead, how long do I have to request a replacement or refund?',
        ]);

        TicketMessage::create([
            'ticket_id' => $t1->id,
            'sender' => 'support',
            'message' => 'Hello! You have a 10-minute auto-checker window on refundable cards in your Orders page. If the card is dead, simply click the Refund button to receive 100% balance back immediately.',
        ]);

        // 4. Seed 60+ Realistic Cards
        $cardSamples = [
            // GB - Revolut
            ['bin' => '416598', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'GB', 'country_name' => 'United Kingdom', 'bank' => 'REVOLUT, LTD.', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => 'SW1A 1AA', 'city' => 'London', 'state' => 'Greater London'],
            ['bin' => '416598', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'GB', 'country_name' => 'United Kingdom', 'bank' => 'REVOLUT, LTD.', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => 'EC1A 1BB', 'city' => 'London', 'state' => 'Greater London'],
            ['bin' => '416598', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'GB', 'country_name' => 'United Kingdom', 'bank' => 'REVOLUT, LTD.', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => 'M1 1AE', 'city' => 'Manchester', 'state' => 'Greater Manchester'],
            ['bin' => '416549', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'GB', 'country_name' => 'United Kingdom', 'bank' => 'REVOLUT, LTD.', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => false, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => 'B1 1AA', 'city' => 'Birmingham', 'state' => 'West Midlands'],
            ['bin' => '416598', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'GB', 'country_name' => 'United Kingdom', 'bank' => 'REVOLUT, LTD.', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => false, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => 'EH1 1YZ', 'city' => 'Edinburgh', 'state' => 'Midlothian'],
            
            // FR - Credit Agricole / BNP
            ['bin' => '513162', 'brand' => 'MASTERCARD', 'type' => 'DEBIT', 'country_code' => 'FR', 'country_name' => 'France', 'bank' => 'CREDIT AGRICOLE S.A.', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => '75008', 'city' => 'Paris', 'state' => 'Ile-de-France'],
            ['bin' => '513162', 'brand' => 'MASTERCARD', 'type' => 'DEBIT', 'country_code' => 'FR', 'country_name' => 'France', 'bank' => 'CREDIT AGRICOLE S.A.', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => false, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => '69002', 'city' => 'Lyon', 'state' => 'Auvergne-Rhone-Alpes'],
            ['bin' => '497043', 'brand' => 'VISA', 'type' => 'CREDIT', 'country_code' => 'FR', 'country_name' => 'France', 'bank' => 'BNP PARIBAS', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.80, 'price_unc' => 2.20, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => true, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => '13001', 'city' => 'Marseille', 'state' => 'Provence-Alpes-Cote d Azur'],
            ['bin' => '540108', 'brand' => 'MASTERCARD', 'type' => 'DEBIT', 'country_code' => 'FR', 'country_name' => 'France', 'bank' => 'SOCIETE GENERALE', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => '33000', 'city' => 'Bordeaux', 'state' => 'Nouvelle-Aquitaine'],

            // BE - Belgium
            ['bin' => '456933', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'BE', 'country_name' => 'Belgium', 'bank' => 'BNP PARIBAS FORTIS', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => false, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => '1000', 'city' => 'Brussels', 'state' => 'Brussels Capital'],
            ['bin' => '456933', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'BE', 'country_name' => 'Belgium', 'bank' => 'BNP PARIBAS FORTIS', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => false, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => '2000', 'city' => 'Antwerp', 'state' => 'Flanders'],
            ['bin' => '487104', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'BE', 'country_name' => 'Belgium', 'bank' => 'BELFIUS BANQUE SA', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => false, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => '9000', 'city' => 'Ghent', 'state' => 'East Flanders'],
            ['bin' => '525547', 'brand' => 'MASTERCARD', 'type' => 'DEBIT', 'country_code' => 'BE', 'country_name' => 'Belgium', 'bank' => 'KBC BANK NV', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.20, 'price_unc' => 1.80, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => '3000', 'city' => 'Leuven', 'state' => 'Flemish Brabant'],

            // US - United States (Fullz / Credit / Debit)
            ['bin' => '424604', 'brand' => 'VISA', 'type' => 'CREDIT', 'country_code' => 'US', 'country_name' => 'United States', 'bank' => 'JPMORGAN CHASE BANK, N.A.', 'base_name' => '2026_08_18_US_UK_MIX (gold***)', 'refundable' => true, 'price_c' => 3.50, 'price_unc' => 2.80, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => true, 'has_dob' => true, 'has_user_agent' => true, 'has_email_password' => true, 'zip' => '90210', 'city' => 'Beverly Hills', 'state' => 'CA'],
            ['bin' => '542418', 'brand' => 'MASTERCARD', 'type' => 'CREDIT', 'country_code' => 'US', 'country_name' => 'United States', 'bank' => 'CITIBANK, N.A.', 'base_name' => '2026_08_18_US_UK_MIX (gold***)', 'refundable' => true, 'price_c' => 3.80, 'price_unc' => 3.00, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => true, 'has_dob' => true, 'has_user_agent' => true, 'has_email_password' => true, 'zip' => '10001', 'city' => 'New York', 'state' => 'NY'],
            ['bin' => '474412', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'US', 'country_name' => 'United States', 'bank' => 'BANK OF AMERICA, N.A.', 'base_name' => '2026_08_18_US_UK_MIX (gold***)', 'refundable' => true, 'price_c' => 2.50, 'price_unc' => 2.00, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => '33101', 'city' => 'Miami', 'state' => 'FL'],
            ['bin' => '400344', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'US', 'country_name' => 'United States', 'bank' => 'WELLS FARGO BANK, N.A.', 'base_name' => '2026_08_18_US_UK_MIX (gold***)', 'refundable' => true, 'price_c' => 2.50, 'price_unc' => 2.00, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => '75201', 'city' => 'Dallas', 'state' => 'TX'],
            ['bin' => '371449', 'brand' => 'AMEX', 'type' => 'CREDIT', 'country_code' => 'US', 'country_name' => 'United States', 'bank' => 'AMERICAN EXPRESS COMPANY', 'base_name' => '2026_08_15_PREMIUM_AMEX (vip***)', 'refundable' => true, 'price_c' => 5.00, 'price_unc' => 4.00, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => true, 'has_dob' => true, 'has_user_agent' => true, 'has_email_password' => true, 'zip' => '60601', 'city' => 'Chicago', 'state' => 'IL'],
            ['bin' => '601100', 'brand' => 'DISCOVER', 'type' => 'CREDIT', 'country_code' => 'US', 'country_name' => 'United States', 'bank' => 'DISCOVER BANK', 'base_name' => '2026_08_18_US_UK_MIX (gold***)', 'refundable' => true, 'price_c' => 3.00, 'price_unc' => 2.40, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => true, 'has_dob' => true, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => '98101', 'city' => 'Seattle', 'state' => 'WA'],

            // DE - Germany
            ['bin' => '448590', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'DE', 'country_name' => 'Germany', 'bank' => 'DEUTSCHE BANK AG', 'base_name' => '2026_08_19_EU_HIGH (war***)', 'refundable' => true, 'price_c' => 2.50, 'price_unc' => 2.00, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => '10115', 'city' => 'Berlin', 'state' => 'Berlin'],
            ['bin' => '535311', 'brand' => 'MASTERCARD', 'type' => 'CREDIT', 'country_code' => 'DE', 'country_name' => 'Germany', 'bank' => 'COMMERZBANK AG', 'base_name' => '2026_08_19_EU_HIGH (war***)', 'refundable' => true, 'price_c' => 3.00, 'price_unc' => 2.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => true, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => '80331', 'city' => 'Munich', 'state' => 'Bavaria'],
            ['bin' => '455610', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'DE', 'country_name' => 'Germany', 'bank' => 'N26 BANK GMBH', 'base_name' => '2026_08_19_EU_HIGH (war***)', 'refundable' => true, 'price_c' => 2.20, 'price_unc' => 1.80, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => '60311', 'city' => 'Frankfurt', 'state' => 'Hesse'],

            // CA - Canada
            ['bin' => '450003', 'brand' => 'VISA', 'type' => 'CREDIT', 'country_code' => 'CA', 'country_name' => 'Canada', 'bank' => 'ROYAL BANK OF CANADA', 'base_name' => '2026_08_18_CA_AU (war***)', 'refundable' => true, 'price_c' => 3.00, 'price_unc' => 2.20, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => true, 'has_dob' => true, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => 'M5V 2T6', 'city' => 'Toronto', 'state' => 'ON'],
            ['bin' => '521900', 'brand' => 'MASTERCARD', 'type' => 'DEBIT', 'country_code' => 'CA', 'country_name' => 'Canada', 'bank' => 'TORONTO-DOMINION BANK', 'base_name' => '2026_08_18_CA_AU (war***)', 'refundable' => true, 'price_c' => 2.50, 'price_unc' => 2.00, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => 'V6B 1A1', 'city' => 'Vancouver', 'state' => 'BC'],

            // AU - Australia
            ['bin' => '516337', 'brand' => 'MASTERCARD', 'type' => 'DEBIT', 'country_code' => 'AU', 'country_name' => 'Australia', 'bank' => 'COMMONWEALTH BANK OF AUSTRALIA', 'base_name' => '2026_08_18_CA_AU (war***)', 'refundable' => true, 'price_c' => 2.70, 'price_unc' => 2.00, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => true, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => '2000', 'city' => 'Sydney', 'state' => 'NSW'],
            ['bin' => '456448', 'brand' => 'VISA', 'type' => 'CREDIT', 'country_code' => 'AU', 'country_name' => 'Australia', 'bank' => 'NATIONAL AUSTRALIA BANK', 'base_name' => '2026_08_18_CA_AU (war***)', 'refundable' => true, 'price_c' => 3.20, 'price_unc' => 2.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => true, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => '3000', 'city' => 'Melbourne', 'state' => 'VIC'],

            // UK - Barclays, Monzo, Lloyds, HSBC, NatWest
            ['bin' => '400349', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'GB', 'country_name' => 'United Kingdom', 'bank' => 'BARCLAYS BANK UK PLC', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => false, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => 'NW1 4NP', 'city' => 'London', 'state' => 'Greater London'],
            ['bin' => '535522', 'brand' => 'MASTERCARD', 'type' => 'DEBIT', 'country_code' => 'GB', 'country_name' => 'United Kingdom', 'bank' => 'MONZO BANK LIMITED', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.20, 'price_unc' => 1.70, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => 'BS1 5DB', 'city' => 'Bristol', 'state' => 'Bristol'],
            ['bin' => '475128', 'brand' => 'VISA', 'type' => 'DEBIT', 'country_code' => 'GB', 'country_name' => 'United Kingdom', 'bank' => 'LLOYDS BANK PLC', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.00, 'price_unc' => 1.50, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => false, 'has_user_agent' => false, 'has_email_password' => false, 'zip' => 'LS1 1BA', 'city' => 'Leeds', 'state' => 'West Yorkshire'],
            ['bin' => '543460', 'brand' => 'MASTERCARD', 'type' => 'CREDIT', 'country_code' => 'GB', 'country_name' => 'United Kingdom', 'bank' => 'HSBC UK BANK PLC', 'base_name' => '2026_08_19_GB_FR_R (war***)', 'refundable' => true, 'price_c' => 2.50, 'price_unc' => 2.00, 'has_name' => true, 'has_address' => true, 'has_zip' => true, 'has_phone' => true, 'has_mail' => true, 'has_ssn' => false, 'has_dob' => true, 'has_user_agent' => true, 'has_email_password' => false, 'zip' => 'CF10 1EP', 'city' => 'Cardiff', 'state' => 'South Glamorgan'],
        ];

        // Let's generate 60 records by varying slightly
        $firstNames = ['James', 'David', 'Sarah', 'Emma', 'Alexandre', 'Camille', 'Julien', 'Sophie', 'Lucas', 'Marc', 'Maximilian', 'Anna', 'Liam', 'Olivia', 'Ethan', 'Chloe', 'Jack', 'Oliver'];
        $lastNames = ['Smith', 'Taylor', 'Dubois', 'Martin', 'Bernard', 'Peeters', 'Janssen', 'Muller', 'Schmidt', 'Johnson', 'Williams', 'Brown', 'Davis', 'Wilson', 'Anderson', 'Thomas'];
        $streets = ['High St', 'Queen Road', 'Rue de Paris', 'Avenue Victor Hugo', 'Wetstraat', 'Kerkstraat', 'Main St', 'Broadway', 'Oak Ave', 'Sunset Blvd', 'King St', 'George St'];

        for ($i = 0; $i < 65; $i++) {
            $base = $cardSamples[$i % count($cardSamples)];
            $fn = $firstNames[($i * 3) % count($firstNames)];
            $ln = $lastNames[($i * 5) % count($lastNames)];
            $street = (12 + $i * 7) . ' ' . $streets[$i % count($streets)];
            
            $expMonth = str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT);
            $expYear = (string)(2027 + ($i % 5));
            $cardNum = $base['bin'] . str_pad(mt_rand(100000000, 999999999), 10, '0', STR_PAD_LEFT);
            if (strlen($cardNum) < 16) {
                $cardNum = str_pad($cardNum, 16, '4', STR_PAD_RIGHT);
            }
            $cvv = str_pad(mt_rand(100, 999), 3, '0', STR_PAD_LEFT);
            $email = strtolower($fn . '.' . $ln . ($i + 10) . '@' . ['gmail.com', 'outlook.com', 'yahoo.com', 'proton.me'][$i % 4]);
            $phone = '+1 (555) ' . mt_rand(200, 999) . '-' . mt_rand(1000, 9999);
            if ($base['country_code'] === 'GB') $phone = '+44 7' . mt_rand(700000000, 999999999);
            if ($base['country_code'] === 'FR') $phone = '+33 6 ' . mt_rand(10, 99) . ' ' . mt_rand(10, 99) . ' ' . mt_rand(10, 99) . ' ' . mt_rand(10, 99);
            if ($base['country_code'] === 'BE') $phone = '+32 4' . mt_rand(70, 99) . ' ' . mt_rand(10, 99) . ' ' . mt_rand(10, 99) . ' ' . mt_rand(10, 99);

            Card::create([
                'bin' => $base['bin'],
                'brand' => $base['brand'],
                'type' => $base['type'],
                'country_code' => $base['country_code'],
                'country_name' => $base['country_name'],
                'has_name' => $base['has_name'],
                'has_address' => $base['has_address'],
                'has_zip' => $base['has_zip'],
                'has_phone' => $base['has_phone'],
                'has_mail' => $base['has_mail'],
                'has_ssn' => $base['has_ssn'],
                'has_dob' => $base['has_dob'],
                'has_user_agent' => $base['has_user_agent'],
                'has_email_password' => $base['has_email_password'],
                'bank' => $base['bank'],
                'base_name' => $base['base_name'],
                'refundable' => $base['refundable'],
                'price_c' => $base['price_c'],
                'price_unc' => $base['price_unc'],
                'card_number' => $cardNum,
                'exp_date' => $expMonth . '/' . substr($expYear, 2),
                'cvv' => $cvv,
                'holder_name' => $fn . ' ' . $ln,
                'address' => $street,
                'city' => $base['city'],
                'state' => $base['state'],
                'zip' => $base['zip'],
                'phone' => $phone,
                'email' => $email,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                'email_password' => $base['has_email_password'] ? 'Pass#' . mt_rand(1000, 9999) : null,
                'status' => 'available',
            ]);
        }
    }
}
